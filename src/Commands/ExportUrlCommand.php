<?php

declare(strict_types=1);

namespace App\Commands;

use App\Application\Outbound\Detour\DetourSpecParser;
use App\Application\Outbound\DTO\UseCase\RunOutboundPipeline\RunOutboundPipelineDTO;
use App\Application\Outbound\DTO\UseCase\SelectOutbounds\SelectOutboundsDTO;
use App\Application\Outbound\Export\Specification\OutboundCoreSupportSpecification;
use App\Application\Outbound\Mapper\ToSchemeString\ToSchemeStringOutboundMapper;
use App\Application\Outbound\Override\OverrideSpecParser;
use App\Application\Outbound\Selection\SelectorComposer;
use App\Application\Outbound\Selection\Syntax\SelectorParser;
use App\Application\Outbound\Urltest\UrltestGroupSpecParser;
use App\Application\Outbound\UseCase\RunOutboundPipeline\RunOutboundPipelineUseCase;
use App\Application\Outbound\UseCase\SelectOutbounds\SelectOutboundsUseCase;
use App\Application\Shared\DTO\UseCase\CreateConfig\CreateConfigDTO;
use App\Application\Shared\DTO\UseCase\SaveConfig\SaveConfigDTO;
use App\Application\Shared\UseCase\CreateConfig\CreateConfigUseCase;
use App\Application\Shared\UseCase\SaveSingBoxConfig\SaveConfigUseCase;
use App\Application\Subscription\DTO\UseCase\FetchSubscriptionContent\SubscriptionContentDTO;
use App\Application\Subscription\DTO\UseCase\FetchSubscriptionContent\SubscriptionContentTypeDTO;
use App\Application\Subscription\Exception\UseCase\FetchSubscriptionContent\UnsupportedSubscriptionContentFormatException;
use App\Application\Subscription\UseCase\CreateOutboundsFromSchemes\CreateOutboundsFromSchemesUseCase;
use App\Application\Subscription\UseCase\FetchSubscriptionContent\FetchSubscriptionContentUseCase;
use App\Commands\Shared\Options\CoreOptionsTrait;
use App\Commands\Shared\Options\DryRunOptionTrait;
use App\Commands\Shared\Options\GlobalSelectionOptionsTrait;
use App\Commands\Shared\Options\Pipeline\PipelineFlagOrder;
use App\Commands\Shared\Options\PipelineOptionsTrait;
use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Shared\Ports\Config\ConfigInstancePort;
use App\Domain\Shared\Ports\IO\Reporter\ReporterInstancePort;
use App\Domain\Shared\ReporterEvent\ReporterEventBuilder;
use App\Domain\Shared\VO\ReporterEvent\ReporterEventAttachmentVO;
use App\Domain\Subscription\Exception\InvalidSubscriptionURLException;
use App\Domain\Subscription\VO\SubscriptionURLVO;
use Iva\ExitCode;
use Iva\Input\Input;
use Iva\Input\Option;
use Iva\Output\Output;
use Iva\Output\Verbosity;
use Psl\Collection\Vector;
use Throwable;
use function Psl\Async\run;

/**
 * Export outbounds straight from one or several subscription urls.
 *
 * Works like `subscription export`, but the source is not the storage: the urls are fetched like in
 * `subscription create`, the responses are handled as ONE response, and neither subscription nor outbounds are saved.
 */
final class ExportUrlCommand extends AbstractCommand
{
    use GlobalSelectionOptionsTrait;
    use CoreOptionsTrait;
    use DryRunOptionTrait;
    use PipelineOptionsTrait;

    private Option $urlOption;
    private Option $schemeOption;
    private ReporterInstancePort $reporter;

    public function __construct(
        ReporterInstancePort                                 $reporterInstancePort,
        ConfigInstancePort                                   $configInstancePort,
        private readonly FetchSubscriptionContentUseCase     $fetchSubscriptionContentUseCase,
        private readonly CreateOutboundsFromSchemesUseCase   $createOutboundsFromSchemesUseCase,
        private readonly SelectOutboundsUseCase              $selectOutboundsUseCase,
        private readonly SelectorParser                      $selectorParser,
        private readonly SelectorComposer                    $selectorComposer,
        private readonly OverrideSpecParser                  $overrideSpecParser,
        private readonly UrltestGroupSpecParser              $urltestGroupSpecParser,
        private readonly DetourSpecParser                    $detourSpecParser,
        private readonly PipelineFlagOrder                   $pipelineFlagOrder,
        private readonly RunOutboundPipelineUseCase          $runOutboundPipelineUseCase,
        private readonly CreateConfigUseCase                 $createConfigUseCase,
        private readonly SaveConfigUseCase                   $saveSingBoxConfigUseCase,
        private readonly ToSchemeStringOutboundMapper        $toSchemeStringOutboundMapper,
    )
    {
        parent::__construct($reporterInstancePort, $configInstancePort);

        $this->reporter = $reporterInstancePort;
    }

    protected function configureCommand(): void
    {
        $this->setName('export-url');
        $this->setDescription('Export outbounds from subscription urls without saving the subscription');

        $this->urlOption = $this->addOption(Option::strings(
            name: 'url',
            description: 'Subscription URL, repeatable: all responses are handled as one response. '
                . 'A url without a response gives a warning, no responses at all is an error. '
                . 'A config response is exported as is and only if it is the only response. '
                . 'Example: --url https://a.example/sub --url https://b.example/sub',
        ));
        $this->schemeOption = $this->addOption(Option::flag('scheme', description: 'Export outbounds as schemes'));

        $this->configureGlobalSelectionOptions();
        $this->configurePipelineOptions();
        $this->configureCoreOptions();
        $this->configureDryRunOption();
    }

    protected function handle(Input $input, Output $output): int
    {
        /**
         * Parse urls and every flag first: syntax errors must appear before any request, selection, DNS or file work
         */
        $urls = $this->resolveUrls($input);

        $exportAsScheme = $input->flag($this->schemeOption);

        $steps = $this->resolvePipelineSteps(
            $input, $this->pipelineFlagOrder, $this->overrideSpecParser, $this->urltestGroupSpecParser, $this->detourSpecParser,
            overridesOnly: $exportAsScheme,
        );

        $selector = $this->resolveGlobalSelector($input, $this->selectorParser, $this->selectorComposer);


        /**
         * Fetch all urls, urls without a response are skipped with a warning
         */
        $responses = $this->fetchResponses($urls, $output);

        if ($responses === []) throw new CriticalException('No response received from any of the urls');


        /**
         * A config can not be merged with anything: it is exported as is, like `subscription export` does,
         * but only when it is the only response
         */
        if (count($responses) === 1 && $responses[0]['content']->contentType === SubscriptionContentTypeDTO::CONFIG) {
            $this->outputResult($responses[0]['content']->content, $input, $output);

            return ExitCode::Ok->value;
        }


        /**
         * Merge schemes of all responses into one response
         */
        $schemes = [];

        foreach ($responses as $response) {
            if ($response['content']->contentType === SubscriptionContentTypeDTO::SCHEMES) {
                $schemes[] = $response['content']->content;

                continue;
            }

            $this->reporter->get()->notify(
                ReporterEventBuilder::warning("Response of {$response['url']} is a config, configs can not be merged with other responses, skipped")->normal()
            );
        }

        if ($schemes === []) throw new CriticalException('No responses with schemes received from the urls');

        $subscriptionOutbounds = $this->createOutboundsFromSchemesUseCase->handle(implode("\n", $schemes));


        /**
         * Select outbounds (main config group)
         */
        $constraints = $exportAsScheme
            ? new Vector([])
            : new Vector([new OutboundCoreSupportSpecification($this->resolveConfigType($input)->toCoreType())]);

        $subscriptionOutbounds = $this->selectOutboundsUseCase->handle(new SelectOutboundsDTO(
            $subscriptionOutbounds,
            selector: $selector,
            constraints: $constraints,
        ));

        if ($subscriptionOutbounds->isEmpty()) throw new CriticalException("No outbound calls matching the selection were found");


        /**
         * Ordered pipeline: override / urltest / detour in the order of flags
         */
        $outbounds = $steps->isEmpty()
            ? $subscriptionOutbounds
            : $this->runOutboundPipelineUseCase->handle(new RunOutboundPipelineDTO(
                $subscriptionOutbounds, $steps, $this->resolveConfigType($input)->toCoreType()
            ));


        if (!$exportAsScheme) {
            $result = $this->createConfigUseCase->handle(
                new CreateConfigDTO($outbounds, $this->resolveConfigType($input))
            );
        } else {
            $result = "";
            foreach ($outbounds->getOutbounds() as $outbound) {
                $result .= $this->toSchemeStringOutboundMapper->map($outbound) . "\n";
            }
        }

        $this->outputResult($result, $input, $output);

        return ExitCode::Ok->value;
    }

    /**
     * Get unique valid urls from the --url option
     *
     * @return SubscriptionURLVO[]
     *
     * @throws CriticalException If no url or invalid url provided
     */
    private function resolveUrls(Input $input): array
    {
        $urls = [];

        foreach (array_values($input->option($this->urlOption)) as $rawUrl) {
            try {
                $url = new SubscriptionURLVO((string)$rawUrl);
            } catch (InvalidSubscriptionURLException $e) {
                throw new CriticalException("Invalid subscription url provided: " . $e->getMessage());
            }

            if (isset($urls[$url->getUrl()])) {
                $this->reporter->get()->notify(
                    ReporterEventBuilder::warning("Url {$url->getUrl()} is provided more than once, the duplicate is skipped")->normal()
                );

                continue;
            }

            $urls[$url->getUrl()] = $url;
        }

        if ($urls === []) throw new CriticalException('At least one url is required: --url <url>');

        return array_values($urls);
    }

    /**
     * Fetch the urls one by one, in the order they were provided
     *
     * @param SubscriptionURLVO[] $urls
     *
     * @return array<int, array{url: string, content: SubscriptionContentDTO}> Responses of the urls that answered
     */
    private function fetchResponses(array $urls, Output $output): array
    {
        $responses = [];
        $fetchUseCase = $this->fetchSubscriptionContentUseCase;

        foreach ($urls as $url) {
            $spinner = $output->spinner("Fetching {$url->getUrl()}...");
            $spinner->start();

            try {
                $content = run(static function () use ($url, $fetchUseCase): SubscriptionContentDTO {
                    return $fetchUseCase->handle($url);
                })->await();
            } catch (UnsupportedSubscriptionContentFormatException $error) {
                $spinner->fail("Unsupported content format: {$url->getUrl()}");

                $this->reporter->get()->notify(
                    ReporterEventBuilder::warning("No response from {$url->getUrl()}: unsupported subscription content format")
                        ->attachments(ReporterEventAttachmentVO::veryVerbose('Content: ' . $error->rawSubscriptionContent))->normal()
                );

                continue;
            } catch (Throwable $error) {
                $spinner->fail("Unable to fetch {$url->getUrl()}");

                $this->reporter->get()->notify(
                    ReporterEventBuilder::warning("No response from {$url->getUrl()}: " . $error->getMessage())->normal()
                );

                continue;
            }

            $spinner->succeed("Fetched {$url->getUrl()}");

            $responses[] = ['url' => $url->getUrl(), 'content' => $content];
        }

        return $responses;
    }

    /**
     * Save result to the config file, or print it with --dry-run (the same as `subscription export`)
     */
    private function outputResult(string $result, Input $input, Output $output): void
    {
        if (!$this->isDryRun($input))
            $this->saveSingBoxConfigUseCase->handle(new SaveConfigDTO($result));
        else $output->writeln($result, Verbosity::Quiet);
    }
}

<?php

declare(strict_types=1);

namespace App\Commands\Subscription;

use App\Application\Repository\Shared\Exception\UnableToGetListException;
use App\Application\Repository\Subscription\GetSubscriptionListRepository;
use App\Application\Subscription\DTO\UseCase\FetchSubscriptionContent\SubscriptionContentDTO;
use App\Application\Subscription\DTO\UseCase\FetchSubscriptionContent\SubscriptionContentTypeDTO;
use App\Application\Subscription\Exception\UseCase\FetchSubscriptionContent\UnsupportedSubscriptionContentFormatException;
use App\Application\Subscription\UseCase\FetchSubscriptionContent\FetchSubscriptionContentUseCase;
use App\Application\Subscription\UseCase\SaveFetchedSubscriptionConfig\SaveFetchedSubscriptionConfigUseCase;
use App\Application\Subscription\UseCase\SaveFetchedSubscriptionSchemes\SaveFetchedSubscriptionSchemesUseCase;
use App\Commands\AbstractCommand;
use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Shared\Ports\Config\ConfigInstancePort;
use App\Domain\Shared\Ports\IO\Reporter\ReporterInstancePort;
use App\Domain\Shared\ReporterEvent\ReporterEventBuilder;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use App\Domain\Subscription\Exception\InvalidSubscriptionURLException;
use App\Domain\Subscription\VO\SubscriptionURLVO;
use InvalidArgumentException;
use Iva\ExitCode;
use Iva\Input\Argument;
use Iva\Input\Input;
use Iva\Input\Option;
use Iva\Output\Output;
use Throwable;
use function Psl\Async\run;

final class CreateSubscriptionCommand extends AbstractCommand
{
    private Argument $nameArgument;
    private Argument $urlArgument;
    private Option $skipDuplicatesOption;

    public function __construct(
        ReporterInstancePort                                   $reporterInstancePort,
        ConfigInstancePort                                     $configInstancePort,
        private readonly GetSubscriptionListRepository         $getSubscriptionListRepository,
        private readonly FetchSubscriptionContentUseCase       $fetchSubscriptionContentUseCase,
        private readonly SaveFetchedSubscriptionSchemesUseCase $saveFetchedSubscriptionSchemesUseCase,
        private readonly SaveFetchedSubscriptionConfigUseCase  $saveFetchedSubscriptionConfigUseCase,
    )
    {
        parent::__construct($reporterInstancePort, $configInstancePort);
    }

    protected function handle(Input $input, Output $output): int
    {
        /**
         * Try to create subscription name and subscription URL
         */
        try {
            /**
             * Create subscription name
             */
            $subscriptionName = new NonEmptyStringVO($input->argument($this->nameArgument));


            /**
             * Create subscription url
             */
            $subscriptionUrl = new SubscriptionUrlVO($input->argument($this->urlArgument));
        } catch (InvalidArgumentException|InvalidSubscriptionURLException $e) {
            throw new CriticalException($e instanceof InvalidArgumentException
                ? "Invalid subscription name provided"
                : "Invalid subscription url provided"
            );
        }


        /**
         * Try to read subscription list
         */
        try {
            $subscriptions = $this->getSubscriptionListRepository->getSubscriptionsList();
        } catch (UnableToGetListException $e) {
            throw new CriticalException ("Unable to add subscription: " . $e->getMessage(), $e->getDebugMessage());
        }


        /**
         * Check subscription with provided name or url already exists
         */
        if ($subscriptions->containsSubscription($subscriptionName))
            throw new CriticalException("Subscription with name {$subscriptionName->getValue()} already exists");


        /**
         * Create spinner
         */
        $spinner = $output->spinner('Fetching subscription...');
        $spinner->start();

        /**
         * Try to fetch subscription content
         */
        try {
            $fetchSubUseCase = $this->fetchSubscriptionContentUseCase;
            $subscriptionContent = run(static function () use ($subscriptionUrl, $fetchSubUseCase): SubscriptionContentDTO {
                return $fetchSubUseCase->handle($subscriptionUrl);
            })->await();
        } catch (UnsupportedSubscriptionContentFormatException $error) {
            $spinner->fail('Unsupported subscription content format');
            throw CriticalException::fromEvents(
                ReporterEventBuilder::error('Unsupported subscription content format')->normal(),
                ReporterEventBuilder::error('Content: ' . $error->rawSubscriptionContent)->debug()
            );
        } catch (Throwable $error) {
            $spinner->fail('Error while fetching subscription content');
            throw new CriticalException('Error: ' . $error->getMessage());
        }


        $spinner->succeed("Subscription fetched successfully");


        /**
         * If subscription content type is schemes list
         */
        if ($subscriptionContent->contentType === SubscriptionContentTypeDTO::SCHEMES)
            $this->saveFetchedSubscriptionSchemesUseCase->handle($subscriptionName, $subscriptionUrl, $subscriptionContent->content, $input->flag($this->skipDuplicatesOption));
        else if ($subscriptionContent->contentType === SubscriptionContentTypeDTO::CONFIG) {
            $this->saveFetchedSubscriptionConfigUseCase->handle($subscriptionName, $subscriptionUrl, $subscriptionContent->content);
        }

        return ExitCode::Ok->value;
    }


    protected function configureCommand(): void
    {
        $this->setName('create');
        $this->setDescription('Create subscription');

        $this->nameArgument = $this->addArgument(Argument::string('name', 'Subscription name'));
        $this->urlArgument = $this->addArgument(Argument::string('url', 'Subscription URL'));
        $this->skipDuplicatesOption = $this->addOption(Option::flag('skipDuplicates', 's'));
    }


}

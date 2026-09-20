<?php

declare(strict_types=1);

namespace App\Commands\Subscription;

use App\Application\Repository\Subscription\GetSubscriptionListRepository;
use App\Application\Repository\Subscription\RemoveSubscriptionRepository;
use App\Application\Subscription\DTO\UseCase\FetchSubscriptionContent\SubscriptionContentTypeDTO;
use App\Application\Subscription\Exception\UseCase\FetchSubscriptionContent\UnsupportedSubscriptionContentFormatException;
use App\Application\Subscription\UseCase\FetchSubscriptionContent\FetchSubscriptionContentUseCase;
use App\Application\Subscription\UseCase\SaveFetchedSubscriptionConfig\SaveFetchedSubscriptionConfigUseCase;
use App\Application\Subscription\UseCase\SaveFetchedSubscriptionSchemes\SaveFetchedSubscriptionSchemesUseCase;
use App\Commands\AbstractCommand;
use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Shared\Ports\Config\ConfigInstancePort;
use App\Domain\Shared\Ports\IO\Reporter\ReporterInstancePort;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use App\Domain\Subscription\Exception\SubscriptionNotFoundException;
use InvalidArgumentException;
use Iva\ExitCode;
use Iva\Input\Argument;
use Iva\Input\Input;
use Iva\Input\Option;
use Iva\Output\Output;

final class UpdateSubscriptionCommand extends AbstractCommand
{
    private Argument $nameArgument;
    private Option $skipDuplicatesOption;

    public function __construct(
        ReporterInstancePort                                   $reporterInstancePort,
        ConfigInstancePort                                     $configInstancePort,
        private readonly GetSubscriptionListRepository         $getSubscriptionListRepository,
        private readonly FetchSubscriptionContentUseCase       $fetchSubscriptionContentUseCase,
        private readonly SaveFetchedSubscriptionConfigUseCase  $saveFetchedSubscriptionConfigUseCase,
        private readonly SaveFetchedSubscriptionSchemesUseCase $saveFetchedSubscriptionSchemesUseCase,
        private readonly RemoveSubscriptionRepository          $removeSubscriptionRepository,
    )
    {
        parent::__construct($reporterInstancePort, $configInstancePort);
    }

    protected function handle(Input $input, Output $output): int
    {
        /**
         * Try to create subscription name
         */
        try {
            /**
             * Create subscription name
             */
            $subscriptionName = new NonEmptyStringVO($input->argument($this->nameArgument));
        } catch (InvalidArgumentException) {
            throw new CriticalException("Invalid subscription name provided");
        }


        /**
         * Try to get subscription with provided name
         */
        try {
            $subscription = $this->getSubscriptionListRepository->getSubscriptionsList()->getSubscriptionByName($subscriptionName);
        } catch (SubscriptionNotFoundException) {
            throw new CriticalException("Subscription with name '{$subscriptionName->getValue()}' not found");
        }

        /**
         * Try to fetch subscription content
         */
        try {
            $subscriptionContent = $this->fetchSubscriptionContentUseCase->handle($subscription->getUrlVO());
        } catch (UnsupportedSubscriptionContentFormatException|InvalidArgumentException $e) {
            throw new CriticalException($e->getMessage());
        }


        /**
         * Remove subscription
         */
        $this->removeSubscriptionRepository->remove($subscription->getNameVO());


        /**
         * If subscription content type is schemes list
         */
        if ($subscriptionContent->contentType === SubscriptionContentTypeDTO::SCHEMES)
            $this->saveFetchedSubscriptionSchemesUseCase->handle($subscriptionName, $subscription->getUrlVO(), $subscriptionContent->content, $input->flag($this->skipDuplicatesOption));
        else if ($subscriptionContent->contentType === SubscriptionContentTypeDTO::CONFIG) {
            $this->saveFetchedSubscriptionConfigUseCase->handle($subscriptionName, $subscription->getUrlVO(), $subscriptionContent->content);
        }

        return ExitCode::Ok->value;
    }

    protected function configureCommand(): void
    {
        $this->setName('update');
        $this->setDescription('Update subscription');

        $this->nameArgument = $this->addArgument(Argument::string('name', 'Subscription name'));
        $this->skipDuplicatesOption = $this->addOption(Option::flag('skipDuplicates', 's'));
    }
}

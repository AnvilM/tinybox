<?php

declare(strict_types=1);

namespace App\Commands\Subscription;

use App\Application\Exception\Subscription\FetchSubscriptionContent\UnsupportedSubscriptionContentFormatException;
use App\Application\Repository\Subscription\GetSubscriptionListRepository;
use App\Application\Repository\Subscription\RemoveSubscriptionRepository;
use App\Application\Subscription\DTO\FetchSubscriptionContent\SubscriptionContentTypeDTO;
use App\Application\Subscription\UseCase\FetchSubscriptionContent\FetchSubscriptionContentUseCase;
use App\Application\Subscription\UseCase\SaveFetchedSubscriptionConfig\SaveFetchedSubscriptionConfigUseCase;
use App\Application\Subscription\UseCase\SaveFetchedSubscriptionSchemes\SaveFetchedSubscriptionSchemesUseCase;
use App\Commands\AbstractCommand;
use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Shared\Ports\Config\ConfigInstancePort;
use App\Domain\Shared\Ports\IO\Reporter\ReporterPort;
use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use App\Domain\Subscription\Exception\SubscriptionNotFoundException;
use InvalidArgumentException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'subscription:update', description: 'Update subscription', aliases: ['sub:update'])]
final class UpdateSubscriptionCommand extends AbstractCommand
{
    public function __construct(
        ReporterPort                                           $reporterPort,
        ConfigInstancePort                                     $configInstancePort,
        private readonly GetSubscriptionListRepository         $getSubscriptionListRepository,
        private readonly FetchSubscriptionContentUseCase       $fetchSubscriptionContentUseCase,
        private readonly SaveFetchedSubscriptionConfigUseCase  $saveFetchedSubscriptionConfigUseCase,
        private readonly SaveFetchedSubscriptionSchemesUseCase $saveFetchedSubscriptionSchemesUseCase,
        private readonly RemoveSubscriptionRepository          $removeSubscriptionRepository,
    )
    {
        parent::__construct($reporterPort, $configInstancePort);
    }

    protected function handle(InputInterface $input, OutputInterface $output): int
    {
        /**
         * Try to create subscription name
         */
        try {
            /**
             * Create subscription name
             */
            $subscriptionName = new NonEmptyStringVO($input->getArgument('name'));
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
            $this->saveFetchedSubscriptionSchemesUseCase->handle($subscriptionName, $subscription->getUrlVO(), $subscriptionContent->content);
        else if ($subscriptionContent->contentType === SubscriptionContentTypeDTO::CONFIG) {
            $this->saveFetchedSubscriptionConfigUseCase->handle($subscriptionName, $subscription->getUrlVO(), $subscriptionContent->content);
        }

        return self::SUCCESS;
    }

    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'Subscription name');
    }
}
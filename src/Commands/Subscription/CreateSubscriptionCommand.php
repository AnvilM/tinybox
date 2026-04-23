<?php

declare(strict_types=1);

namespace App\Commands\Subscription;

use App\Application\Exception\Repository\Shared\UnableToGetListException;
use App\Application\Exception\Subscription\FetchSubscriptionContent\UnsupportedSubscriptionContentFormatException;
use App\Application\Repository\Subscription\GetSubscriptionListRepository;
use App\Application\Subscription\DTO\FetchSubscriptionContent\SubscriptionContentTypeDTO;
use App\Application\Subscription\UseCase\FetchSubscriptionContent\FetchSubscriptionContentUseCase;
use App\Application\Subscription\UseCase\SaveFetchedSubscriptionSchemes\SaveFetchedSubscriptionSchemesUseCase;
use App\Commands\AbstractCommand;
use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Shared\Ports\Config\ConfigInstancePort;
use App\Domain\Shared\Ports\IO\Reporter\ReporterPort;
use App\Domain\Subscription\Exception\InvalidSubscriptionNameException;
use App\Domain\Subscription\Exception\InvalidSubscriptionURLException;
use App\Domain\Subscription\VO\SubscriptionNameVO;
use App\Domain\Subscription\VO\SubscriptionURLVO;
use InvalidArgumentException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'subscription:create', description: 'Create subscription', aliases: ['sub:create'])]
final class CreateSubscriptionCommand extends AbstractCommand
{
    public function __construct(
        ReporterPort                                           $reporterPort,
        ConfigInstancePort                                     $configInstancePort,
        private readonly GetSubscriptionListRepository         $getSubscriptionListRepository,
        private readonly FetchSubscriptionContentUseCase       $fetchSubscriptionContentUseCase,
        private readonly SaveFetchedSubscriptionSchemesUseCase $saveFetchedSubscriptionSchemesUseCase,
    )
    {
        parent::__construct($reporterPort, $configInstancePort);
    }

    protected function handle(InputInterface $input, OutputInterface $output): int
    {
        /**
         * Try to create subscription name and subscription URL
         */
        try {
            /**
             * Create subscription name
             */
            $subscriptionName = new SubscriptionNameVO($input->getArgument('name'));


            /**
             * Create subscription url
             */
            $subscriptionUrl = new SubscriptionUrlVO($input->getArgument('url'));
        } catch (InvalidSubscriptionNameException|InvalidSubscriptionURLException $e) {
            throw new CriticalException($e instanceof InvalidSubscriptionNameException
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
        if ($subscriptions->containsSubscriptionUrlOrName($subscriptionUrl, $subscriptionName))
            throw new CriticalException("Subscription with name {$subscriptionName->getName()} or url {$subscriptionUrl->getUrl()} already exists");


        /**
         * Try to fetch subscription content
         */
        try {
            $subscriptionContent = $this->fetchSubscriptionContentUseCase->handle($subscriptionUrl);
        } catch (UnsupportedSubscriptionContentFormatException|InvalidArgumentException $e) {
            throw new CriticalException($e->getMessage());
        }


        /**
         * If subscription content type is schemes list
         */
        if ($subscriptionContent->contentType === SubscriptionContentTypeDTO::SCHEMES)
            $this->saveFetchedSubscriptionSchemesUseCase->handle($subscriptionName, $subscriptionUrl, $subscriptionContent->content);
        else if ($subscriptionContent->contentType === SubscriptionContentTypeDTO::CONFIG) {
            // TODO: Add config logic
        }


        return self::SUCCESS;
    }


    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'Subscription name')
            ->addArgument('url', InputArgument::REQUIRED, 'Subscription URL');
    }


}
<?php

declare(strict_types=1);

namespace App\Commands;

use App\Application\UpdateSubscriptions\Command\UpdateSubscriptionsCommand;
use App\Application\UpdateSubscriptions\Handler\UpdateSubscriptionsHandler;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


#[AsCommand(name: 'update')]
final  class UpdateCommand extends Command
{
    public function __construct(
        private UpdateSubscriptionsHandler $updateSubscriptionsHandler,
    )
    {
        parent::__construct();
    }

    public function __invoke(InputInterface $input, OutputInterface $output): int
    {
        $this->updateSubscriptionsHandler->handle(
            new UpdateSubscriptionsCommand(null)
        );

        return Command::SUCCESS;

//        try {
//            $parsedSchemes = $this->fetchSubscriptionSchemes->fetchSubscriptionSchemes(
//                $this->loadSubscriptionList->loadSubscriptionList()
//            );
//
//            $configMap = new SingBoxConfigMap();
//
//            foreach ($parsedSchemes as $name => $schemes) {
//                $configMap[$name] = $this->generateSingBoxConfig->generateSingBoxConfig($schemes);
//            }
//
//            $this->saveSingBoxConfig->saveSingBoxConfig($configMap);
//
//
//        } catch (ApplicationException $exception) {
//            $output->writeln('<error>' . $exception->getMessage() . '</error>');
//            return Command::FAILURE;
//        } catch (Exception $e) {
//            $output->writeln('<error>' . $e->getMessage() . '</error>');
//            return Command::FAILURE;
//        }
//
//        return Command::SUCCESS;
    }

    protected function configure(): void
    {
        $this->addOption(
            'debug',
            'd',
            InputOption::VALUE_NONE,
            'Show update list'
        );
    }
}
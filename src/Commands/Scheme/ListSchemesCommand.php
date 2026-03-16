<?php

declare(strict_types=1);

namespace App\Commands\Scheme;

use App\Application\Services\Scheme\ListSchemes\Handler\ListSchemesHandler;
use App\Commands\AbstractCommand;
use App\Domain\Scheme\Entity\Scheme;
use App\Domain\Shared\Ports\IO\Reporter\ReporterPort;
use Application\Config\ApplicationConfig\ApplicationConfig;
use League\CLImate\CLImate;
use Psl\Collection\MutableMap;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function Psl\Str\length;

#[AsCommand(name: 'scheme:list', description: 'List schemes', aliases: ['sc:list'])]
final class ListSchemesCommand extends AbstractCommand
{
    public function __construct(
        ReporterPort                        $reporterPort,
        private readonly ListSchemesHandler $listSchemesHandler,
    )
    {
        parent::__construct($reporterPort);
    }

    protected function handle(InputInterface $input, OutputInterface $output): int
    {
        /** @var MutableMap<string, Scheme> $schemeMap */
        $schemeMap = $this->listSchemesHandler->handle();

        $longestIdLength = 0;
        $longestTagLength = 0;

        foreach ($schemeMap as $scheme) {
            if (length($scheme->getHash()) > $longestIdLength) $longestIdLength = length($scheme->getHash());
            if (length($scheme->getTag()) > $longestTagLength) $longestTagLength = length($scheme->getTag());
        }

        new CLImate()->inline('     ');
        new CLImate()->inline('ID');
        for ($i = 0; $i < $longestIdLength; ++$i) {
            new CLImate()->inline(' ');
        }
        new CLImate()->inline('Tag');
        new CLImate()->br();


        foreach ($schemeMap as $scheme) {

            new CLImate()->green()->inline('[+]');
            new CLImate()->inline('  ');
            $idLength = length($scheme->getHash());
            new CLImate()->green()->inline($scheme->getHash());
            for ($i = 0; $i < $longestIdLength - $idLength + 2; ++$i) {
                new CLImate()->inline(' ');
            }
            new CLImate()->green()->inline($scheme->getTag());


            if (ApplicationConfig::isDebug()) {
                new CLImate()->br();
                new CLImate()->out($scheme->toRawScheme());
            }

            new CLImate()->br();

        }

        return Command::SUCCESS;
    }

    protected function configure(): void
    {
        $this->addOption('debug', 'd', InputOption::VALUE_NONE, 'Show debug messages');
    }
}
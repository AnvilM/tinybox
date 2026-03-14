<?php

declare(strict_types=1);

namespace App\Commands;

use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Shared\Ports\IO\Reporter\ReporterPort;
use App\Domain\Shared\ReporterEvent\Events\Shared\FatalErrorReporterEvent;
use App\Domain\Shared\VO\ReporterEvent\ReporterEventDebugMessagesVO;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

abstract class AbstractCommand extends Command
{
    public function __construct(
        private readonly ReporterPort $reporterPort,
    )
    {
        parent::__construct();
    }

    public function __invoke(InputInterface $input, OutputInterface $output): int
    {
        try {
            return $this->handle($input, $output);
        } catch (CriticalException $e) {
            $this->reporterPort->notify(new FatalErrorReporterEvent(
                $e->getMessage(),
                $e->debugMessage ? new ReporterEventDebugMessagesVO([$e->debugMessage]) : null
            ));

            return Command::FAILURE;
        } catch (Throwable $e) {
            $this->reporterPort->notify(new FatalErrorReporterEvent(
                "Unhandled exception",
                new ReporterEventDebugMessagesVO([$e->getMessage()])
            ));
        }

        return Command::FAILURE;
    }

    /**
     * @throws CriticalException
     * @throws Throwable
     */
    protected abstract function handle(InputInterface $input, OutputInterface $output): int;
}
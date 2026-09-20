<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared\IO\Reporter;

use App\Domain\Shared\Ports\IO\Reporter\ReporterPort;
use App\Domain\Shared\ReporterEvent\ReporterEventInterface;
use App\Domain\Shared\VO\ReporterEvent\ReporterEventTypeVO;
use App\Infrastructure\Shared\IO\Reporter\Output\CLI;
use Iva\Output\Output;
use Iva\Output\Verbosity;

final readonly class Reporter implements ReporterPort
{
    private CLI $cli;

    private function __construct(Output $output)
    {
        $this->cli = new CLI($output);
    }


    public static function fromOutput(Output $output): self
    {
        return new self($output);
    }

    public function notify(ReporterEventInterface ...$reporterEvents): void
    {

        foreach ($reporterEvents as $reporterEvent) {
            $formatedMessage = $reporterEvent->getType()->value . ' ';

            $formatedMessage .= $reporterEvent->getMessage();

            $verbosity = Verbosity::tryFrom($reporterEvent->getVerbosity()->value);

            switch ($reporterEvent->getType()) {
                case ReporterEventTypeVO::Success:
                    $this->cli->out("<green>$formatedMessage</green>", $verbosity, $reporterEvent->getAttachments());
                    break;
                case ReporterEventTypeVO::Skipped:
                    $this->cli->out("<light_yellow>$formatedMessage</light_yellow>", $verbosity, $reporterEvent->getAttachments());
                    break;
                case ReporterEventTypeVO::Warning:
                    $this->cli->out("<yellow>$formatedMessage</yellow>", $verbosity, $reporterEvent->getAttachments());
                    break;
                case ReporterEventTypeVO::Error:
                    $this->cli->out("<red>$formatedMessage</red>", $verbosity, $reporterEvent->getAttachments());
                    break;
                case ReporterEventTypeVO::Step:
                    $this->cli->out("<blue>$formatedMessage</blue>", $verbosity, $reporterEvent->getAttachments());
            }
        }
    }

}
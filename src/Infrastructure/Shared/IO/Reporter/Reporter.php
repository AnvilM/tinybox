<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared\IO\Reporter;

use App\Domain\Shared\Ports\IO\Reporter\ReporterPort;
use App\Domain\Shared\ReporterEvent\ReporterEventInterface;
use App\Domain\Shared\VO\ReporterEvent\ReporterEventDebugMessagesVO;
use App\Domain\Shared\VO\ReporterEvent\ReporterEventTypeVO;
use App\Infrastructure\Shared\IO\Reporter\Output\CLI;

final readonly class Reporter implements ReporterPort
{
    public function __construct(
        private CLI $output,
    )
    {
    }

    public function notify(ReporterEventInterface $reporterEvent): void
    {
        $formatedMessage = $reporterEvent->getType()->value . ' ';

        if ($reporterEvent->getBreadcrumbsVO()) foreach ($reporterEvent->getBreadcrumbsVO() as $breadcrumb) {
            $formatedMessage .= "[$breadcrumb] ";
        }

        $formatedMessage .= $reporterEvent->getMessage();

        $formateDebugMessages = function (?ReporterEventDebugMessagesVO $debugMessagesVO): ?string {
            if ($debugMessagesVO === null) return null;
            $formatedDebugMessagesString = "";

            foreach ($debugMessagesVO as $key => $debugMessage) {
                $formatedDebugMessagesString .= $key === count($debugMessagesVO) - 1 ? "$debugMessage" : "$debugMessage\n";
            }

            return $formatedDebugMessagesString;
        };

        switch ($reporterEvent->getType()) {
            case ReporterEventTypeVO::Success:
                $this->output->out("<green>$formatedMessage</green>", $formateDebugMessages($reporterEvent->getDebugMessage()) ?? '');
                break;
            case ReporterEventTypeVO::Skipped:
                $this->output->err("<light_yellow>$formatedMessage</light_yellow>", $formateDebugMessages($reporterEvent->getDebugMessage()) ?? '');
                break;
            case ReporterEventTypeVO::Warning:
                $this->output->err("<yellow>$formatedMessage</yellow>", $formateDebugMessages($reporterEvent->getDebugMessage()) ?? '');
                break;
            case ReporterEventTypeVO::Error:
                $this->output->err("<red>$formatedMessage</red>", $formateDebugMessages($reporterEvent->getDebugMessage()) ?? '');
                break;
            case ReporterEventTypeVO::Step:
                $this->output->err("<blue>$formatedMessage</blue>", $formateDebugMessages($reporterEvent->getDebugMessage()) ?? '');
        }
    }

}
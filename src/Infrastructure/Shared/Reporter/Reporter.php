<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared\Reporter;

use App\Core\Shared\Ports\Output\OutputPort;
use App\Core\Shared\Ports\Reporter\ReporterPort;
use App\Core\Shared\ReporterEvent\ReporterEventInterface;
use App\Core\Shared\VO\ReporterEvent\DebugMessagesVO;
use App\Core\Shared\VO\ReporterEvent\TypeVO;

final readonly class Reporter implements ReporterPort
{
    public function __construct(
        private OutputPort $outputPort,
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

        $formateDebugMessages = function (?DebugMessagesVO $debugMessagesVO): ?string {
            if ($debugMessagesVO === null) return null;
            $formatedDebugMessagesString = "";

            foreach ($debugMessagesVO as $key => $debugMessage) {
                $formatedDebugMessagesString .= $key === count($debugMessagesVO) - 1 ? "$debugMessage" : "$debugMessage\n";
            }

            return $formatedDebugMessagesString;
        };

        switch ($reporterEvent->getType()) {
            case TypeVO::Success:
                $this->outputPort->out("<green>$formatedMessage</green>", $formateDebugMessages($reporterEvent->getDebugMessage()) ?? '');
                break;
            case TypeVO::Skipped:
            case TypeVO::Warning:
                $this->outputPort->err("<yellow>$formatedMessage</yellow>", $formateDebugMessages($reporterEvent->getDebugMessage()) ?? '');
                break;
            case TypeVO::Error:
                $this->outputPort->err("<red>$formatedMessage</red>", $formateDebugMessages($reporterEvent->getDebugMessage()) ?? '');
                break;
            case TypeVO::Step:
                $this->outputPort->err("<blue>$formatedMessage</blue>", $formateDebugMessages($reporterEvent->getDebugMessage()) ?? '');
        }

        $this->outputPort->br();
    }

}
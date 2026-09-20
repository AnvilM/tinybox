<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared\IO\Reporter\Output;

use App\Domain\Shared\VO\ReporterEvent\ReporterEventAttachmentVO;
use Iva\Output\Formatter\Color;
use Iva\Output\Formatter\Style;
use Iva\Output\Output;
use Iva\Output\Terminal\ColorSupport;
use Iva\Output\Verbosity;

final class CLI
{
    public function __construct(private Output $output)
    {
        $this->output = $this->output->withColorSupport(ColorSupport::Ansi16);

        $this->output->defineStyle('green', new Style(Color::parse('green')));
        $this->output->defineStyle('yellow', new Style(Color::parse('yellow')));
        $this->output->defineStyle('light_yellow', new Style(Color::parse('light_yellow')));
        $this->output->defineStyle('red', new Style(Color::parse('red')));
        $this->output->defineStyle('blue', new Style(Color::parse('blue')));
    }

    public function err($message, $messagw)
    {
        $this->out($message, $messagw);
    }

    /**
     * Prints message to stdout
     *
     * @param string $message Message to print
     * @param Verbosity $verbosity Debug message to print
     * @param ReporterEventAttachmentVO[] $attachments Reporter event attachments
     *
     * @return CLI
     */
    public function out(string $message, Verbosity $verbosity, array $attachments = []): CLI
    {
        $this->output->writeln($message, minVerbosity: $verbosity);

        foreach ($attachments as $attachment) {
            $this->output->writeln('    ' . $attachment->message, minVerbosity: Verbosity::tryFrom($attachment->verbosity->value));
        }

        return $this;
    }
}
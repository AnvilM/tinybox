<?php

declare(strict_types=1);

namespace App\Commands;

use App\Commands\Shared\Options\BaseOptionsTrait;
use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Shared\Ports\Config\ConfigInstancePort;
use App\Domain\Shared\ReporterEvent\ReporterEventBuilder;
use App\Infrastructure\Shared\IO\Reporter\ReporterInstance;
use Iva\Command\Command;
use Iva\ExitCode;
use Iva\Input\Input;
use Iva\Output\Output;
use Throwable;


abstract class AbstractCommand extends Command
{
    use BaseOptionsTrait;

    public function __construct(
        private readonly ReporterInstance     $reporterInstancePort,
        protected readonly ConfigInstancePort $configInstancePort,
    )
    {
        parent::__construct();
    }

    final public function execute(Input $input, Output $output): int
    {
        /**
         * Create reporter instance
         */
        $this->reporterInstancePort->set($output);

        /**
         * Create config
         */
        $this->configInstancePort->set(
            $this->resolveConfigPath($input),
            $this->resolveConfigOptions($input),
        );


        /**
         * Try to handle command
         */
        try {
            return $this->handle($input, $output);
        } catch (CriticalException $e) {
            if (trim($e->getMessage()) !== '') {
                $this->reporterInstancePort->get()->notify(ReporterEventBuilder::error($e->getMessage())->normal());
            }

            if (trim($e->getDebugMessage()) !== '') {
                $this->reporterInstancePort->get()->notify(ReporterEventBuilder::error($e->getDebugMessage())->normal());
            }


            if ($e->events) {
                foreach ($e->events as $event) {
                    $this->reporterInstancePort->get()->notify($event);
                }
            }
            return ExitCode::GeneralError->value;
        }
    }

    /**
     * @throws CriticalException
     * @throws Throwable
     */
    abstract protected function handle(Input $input, Output $output): int;

    final protected function configure(): void
    {
        $this->configureBaseOptions();
        $this->configureCommand();
    }

    /**
     * Register this command's own name/description/aliases and its
     * arguments/options here. Direct replacement for what used to be
     * configure() before every command got the base options injected
     * automatically by this class.
     */
    abstract protected function configureCommand(): void;
}

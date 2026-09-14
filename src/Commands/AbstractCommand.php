<?php

declare(strict_types=1);

namespace App\Commands;

use App\Commands\Shared\Interface\OptionGroup\OptionGroupInterface;
use App\Commands\Shared\OptionGroup\Groups\BaseOptionsGroup;
use App\Commands\Shared\OptionGroup\OptionGroupRegistry;
use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Shared\Ports\Config\ConfigInstancePort;
use App\Domain\Shared\Ports\IO\Reporter\ReporterPort;
use App\Domain\Shared\ReporterEvent\Events\Shared\FatalErrorReporterEvent;
use App\Domain\Shared\VO\ReporterEvent\ReporterEventDebugMessagesVO;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

abstract class AbstractCommand extends Command
{
    protected readonly OptionGroupRegistry $optionGroups;

    public function __construct(
        private readonly ReporterPort         $reporterPort,
        protected readonly ConfigInstancePort $configInstancePort,
    )
    {
        parent::__construct();


        /**
         * Create options groups registry
         */
        $this->optionGroups = new OptionGroupRegistry($this);


        /**
         * Register base options group in options groups registry
         */
        $this->optionGroups->register(new BaseOptionsGroup());


        /**
         * Register command specific options groups in registry
         */
        foreach ($this->optionGroups() as $group) {
            $this->optionGroups->register($group);
        }
    }

    /**
     * Override in specific command with needed options groups
     *
     * @return OptionGroupInterface[] Options groups array
     */
    protected function optionGroups(): array
    {
        return [];
    }


    public function __invoke(InputInterface $input, OutputInterface $output): int
    {
        /**
         * Set input for all registered options groups
         */
        $this->optionGroups->setInput($input);


        /**
         * Create config
         */
        $configOptions = $this->optionGroups->get(BaseOptionsGroup::class)->getConfigOptions();
        $this->configInstancePort->set($this->optionGroups->get(BaseOptionsGroup::class)->getConfigPath(), $configOptions);


        /**
         * Try to handle command
         */
        try {
            return $this->handle($input, $output);
        } catch (CriticalException $e) {
            $this->reporterPort->notify(new FatalErrorReporterEvent(
                $e->getMessage(),
                $e->debugMessage ? new ReporterEventDebugMessagesVO([$e->debugMessage]) : null
            ));

            return Command::FAILURE;
        }
    }

    /**
     * @throws CriticalException
     * @throws Throwable
     */
    protected abstract function handle(InputInterface $input, OutputInterface $output): int;
}
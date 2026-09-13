<?php

declare(strict_types=1);

namespace App\Commands;

use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Shared\Ports\Config\ConfigInstancePort;
use App\Domain\Shared\Ports\IO\Reporter\ReporterPort;
use App\Domain\Shared\ReporterEvent\Events\Shared\FatalErrorReporterEvent;
use App\Domain\Shared\VO\ReporterEvent\ReporterEventDebugMessagesVO;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

abstract class AbstractCommand extends Command
{
    public function __construct(
        private readonly ReporterPort         $reporterPort,
        protected readonly ConfigInstancePort $configInstancePort,
    )
    {
        parent::__construct();

        $this->addOption('debug', 'd', InputOption::VALUE_NONE, 'Show debug messages');
        $this->addOption('configPath', 'c', InputOption::VALUE_OPTIONAL, 'Config path');
        $this->addOption('configOption', 'o', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Config option');
    }

    public function __invoke(InputInterface $input, OutputInterface $output): int
    {

        $configOptions = $input->getOption('configOption') ? $this->parseConfigOptions(
            $input->getOption('configOption')
        ) : null;


        try {
            /**
             * Create config
             */
            $this->configInstancePort->set($input->getOption('configPath'), $configOptions);

            return $this->handle($input, $output);
        } catch (CriticalException $e) {
            $this->reporterPort->notify(new FatalErrorReporterEvent(
                $e->getMessage(),
                $e->debugMessage ? new ReporterEventDebugMessagesVO([$e->debugMessage]) : null
            ));

            return Command::FAILURE;
        }
    }

    private function parseConfigOptions(array $items): array
    {
        $result = [];

        foreach ($items as $item) {
            [$path, $value] = explode('=', $item, 2);

            $keys = explode('.', $path);

            $value = match (true) {
                $value === 'true' => true,
                $value === 'false' => false,
                $value === 'null' => null,
                is_numeric($value) => str_contains($value, '.') ? (float)$value : (int)$value,
                strlen($value) >= 2 && $value[0] === '"' && $value[-1] === '"' =>
                substr($value, 1, -1),
                default => $value,
            };

            $current = &$result;

            foreach ($keys as $key) {
                $current = &$current[$key];
            }

            $current = $value;

            unset($current);
        }

        return $result;
    }

    /**
     * @throws CriticalException
     * @throws Throwable
     */
    protected abstract function handle(InputInterface $input, OutputInterface $output): int;
}
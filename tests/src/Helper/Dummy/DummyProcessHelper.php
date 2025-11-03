<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\JunitMerger\Tests\Helper\Dummy;

use Symfony\Component\Console\Helper\ProcessHelper;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * @phpstan-ignore-next-line
 */
class DummyProcessHelper extends ProcessHelper
{
    /**
     * @phpstan-param array<string>|\Symfony\Component\Process\Process $cmd
     */
    public function run(
        OutputInterface $output,
        array|Process $cmd,
        ?string $error = null,
        ?callable $callback = null,
        int $verbosity = OutputInterface::VERBOSITY_VERY_VERBOSE,
    ): Process {
        $cwd = null;
        $envVars = null;
        $input = null;
        $timeout = null;

        if ($cmd instanceof Process) {
            $cwd = $cmd->getWorkingDirectory();
            $envVars = $cmd->getEnv();
            $timeout = $cmd->getTimeout();
            $input = $cmd->getInput();

            $cmd = $cmd->getCommandLine();
        }

        if (is_string($cmd)) {
            return DummyProcess::fromShellCommandline($cmd, $cwd, $envVars, $input, $timeout);
        }

        $process = new DummyProcess($cmd, $cwd, $envVars, $input, $timeout);
        $process->run($callback);

        return $process;
    }
}

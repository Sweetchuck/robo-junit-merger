<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\JunitMerger\Tests\Acceptance\Task;

use PHPUnit\Framework\TestCase;
use Robo\Robo;
use Robo\Runner;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

class TestBase extends TestCase
{

    protected static function selfProjectRoot(): string
    {
        return dirname(__DIR__, 4);
    }

    protected static function getFixturesDir(): string
    {
        return static::selfProjectRoot() . '/tests/fixtures';
    }

    /**
     * @return array{output: \Symfony\Component\Console\Output\BufferedOutput, exitCode: int}
     */
    public function runRoboTask(
        string $class,
        string ...$args
    ): array {
        $config = [
            'verbosity' => OutputInterface::VERBOSITY_DEBUG,
            'colors' => false,
        ];

        $result = [
            'output' => new BufferedOutput(),
            'exitCode' => 0,
        ];

        array_unshift($args, 'RoboTaskRunner.php', '--no-ansi');

        $containerBackup = Robo::hasContainer() ? Robo::getContainer() : null;
        if ($containerBackup) {
            Robo::unsetContainer();
        }

        $container = Robo::createDefaultContainer(null, $result['output']);
        // @phpstan-ignore-next-line
        $container->add('output', $result['output'], false);
        Robo::setContainer($container);

        $runner = new Runner($class);
        // @phpstan-ignore-next-line
        $runner->setContainer($container);
        $result['exitCode'] = $runner->execute($args);

        if ($containerBackup) {
            Robo::setContainer($containerBackup);
        } else {
            Robo::unsetContainer();
        }

        return $result;
    }
}

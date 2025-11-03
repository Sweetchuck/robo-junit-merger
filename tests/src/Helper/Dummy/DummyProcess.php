<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\JunitMerger\Tests\Helper\Dummy;

use Symfony\Component\Process\Process;

class DummyProcess extends Process
{
    /**
     * @var array<array{exitCode: int, stdOutput: string, stdError: string}>
     */
    public static array $prophecy = [];

    protected static int $counter = 0;

    /**
     * @var static[]
     */
    public static array $instances = [];

    public static function reset(): void
    {
        static::$counter = 0;
        static::$prophecy = [];
        static::$instances = [];
    }

    protected int $index = 0;

    /**
     * {@inheritdoc}
     *
     * @phpstan-param array<string, string> $command
     * @phpstan-param array<string, string> $env
     */
    public function __construct(
        array $command,
        ?string $cwd = null,
        ?array $env = null,
        mixed $input = null,
        ?float $timeout = 60,
    ) {
        parent::__construct($command, $cwd, $env, $input, $timeout);

        $this->index = static::$counter++;
        static::$instances[$this->index] = $this;

        if (!array_key_exists($this->index, static::$prophecy)) {
            throw new \LogicException(sprintf(
                'DummyProcess: there is no prepared prophecy for instance %d; command: %s',
                $this->index,
                $this->getCommandLine(),
            ));
        }
    }

    public function __destruct()
    {
        parent::__destruct();

        unset(static::$instances[$this->index]);
        unset(static::$prophecy[$this->index]);
    }

    /**
     * {@inheritdoc}
     *
     * @phpstan-param array<string, string> $env
     *
     * @phpstan-ignore-next-line
     */
    public function run(?callable $callback = null, array $env = []): int
    {
        if ($callback) {
            if (static::$prophecy[$this->index]['stdOutput']) {
                $callback(static::OUT, static::$prophecy[$this->index]['stdOutput']);
            }

            if (static::$prophecy[$this->index]['stdError']) {
                $callback(static::ERR, static::$prophecy[$this->index]['stdError']);
            }
        }

        return static::$prophecy[$this->index]['exitCode'];
    }

    /**
     * {@inheritdoc}
     */
    public function getExitCode(): ?int
    {
        return static::$prophecy[$this->index]['exitCode'];
    }

    /**
     * {@inheritdoc}
     */
    public function getOutput(): string
    {
        return static::$prophecy[$this->index]['stdOutput'];
    }

    /**
     * {@inheritdoc}
     */
    public function getErrorOutput(): string
    {
        return static::$prophecy[$this->index]['stdError'];
    }
}

<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\JunitMerger\Tests\Unit\Task;

use League\Container\Container as LeagueContainer;
use PHPUnit\Framework\TestCase;
use Robo\Collection\CollectionBuilder;
use Robo\Config\Config as RoboConfig;
use Robo\Robo;
use Sweetchuck\Robo\JunitMerger\Tests\Helper\Dummy\DummyProcess;
use Sweetchuck\Robo\JunitMerger\Tests\Helper\Dummy\DummyProcessHelper;
use Sweetchuck\Robo\JunitMerger\Tests\Helper\Dummy\DummyTaskBuilder;
use Sweetchuck\Robo\JunitMerger\Tests\UnitTester;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\ErrorHandler\BufferingLogger;

abstract class TaskTestBase extends TestCase
{
    /**
     * @var \Psr\Container\ContainerInterface
     */
    protected $container;

    protected RoboConfig $config;

    protected CollectionBuilder $builder;

    /**
     * @var \Sweetchuck\Robo\JunitMerger\Task\BaseTask
     */
    protected $task;

    protected DummyTaskBuilder $taskBuilder;

    protected static function selfProjectRoot(): string
    {
        return dirname(__DIR__, 4);
    }

    protected static function getFixturesDir(): string
    {
        return static::selfProjectRoot() . '/tests/fixtures';
    }

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        Robo::unsetContainer();
        DummyProcess::reset();

        $this->container = new LeagueContainer();
        $application = new SymfonyApplication('Sweetchuck - Robo JUnit Merger', '1.0.0');
        $application->getHelperSet()->set(new DummyProcessHelper(), 'process');
        $this->config = new RoboConfig();
        $input = null;
        $output = new BufferedOutput(
            OutputInterface::VERBOSITY_DEBUG,
            false,
        );

        $this->container->add('container', $this->container);

        Robo::configureContainer($this->container, $application, $this->config, $input, $output);
        $this->container->add('logger', BufferingLogger::class);

        // @phpstan-ignore-next-line
        $this->builder = CollectionBuilder::create($this->container, null);
        $this->taskBuilder = new DummyTaskBuilder();
        $this->taskBuilder->setContainer($this->container);
        $this->taskBuilder->setBuilder($this->builder);

        $this->initTask();
    }

    abstract protected function initTask(): static;
}

<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\JunitMerger\Task;

use Robo\Result;
use Robo\Task\BaseTask as RoboBaseTask;
use Robo\TaskInfo;

abstract class BaseTask extends RoboBaseTask
{
    protected string $taskName = 'JUnit merger';

    /**
     * @var array<string, mixed>
     */
    protected array $assets = [];

    protected int $taskResultCode = 0;

    public function __construct()
    {
        //
    }

    public function __toString()
    {
        return $this->taskName;
    }

    /**
     * @param array<string, mixed> $options
     */
    public function setOptions(array $options): static
    {
        if (array_key_exists('assetNamePrefix', $options)) {
            $this->setAssetNamePrefix($options['assetNamePrefix']);
        }

        return $this;
    }

    protected string $assetNamePrefix = '';

    public function getAssetNamePrefix(): string
    {
        return $this->assetNamePrefix;
    }

    public function setAssetNamePrefix(string $assetNamePrefix): static
    {
        $this->assetNamePrefix = $assetNamePrefix;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        return $this
            ->runInit()
            ->runHeader()
            ->runInitAssets()
            ->runDoIt()
            ->runReturn();
    }

    protected function runInit(): static
    {
        return $this;
    }

    protected function runHeader(): static
    {
        $this->printTaskInfo('is working');

        return $this;
    }

    protected function runInitAssets(): static
    {
        $this->assets = [];

        return $this;
    }

    abstract protected function runDoIt(): static;

    protected function runReturn(): Result
    {
        return new Result(
            $this,
            $this->taskResultCode,
            $this->getTaskResultMessage(),
            $this->getAssetsWithPrefixedNames()
        );
    }

    protected function getTaskResultMessage(): string
    {
        return '';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getAssetsWithPrefixedNames(): array
    {
        $prefix = $this->getAssetNamePrefix();
        if (!$prefix) {
            return $this->assets;
        }

        $assets = [];
        foreach ($this->assets as $key => $value) {
            $assets["{$prefix}{$key}"] = $value;
        }

        return $assets;
    }

    public function getTaskName(): string
    {
        return $this->taskName ?: TaskInfo::formatTaskName($this);
    }

    /**
     * {@inheritdoc}
     *
     * @phpstan-param null|array<string, mixed> $context
     *
     * @return array<string, mixed>
     */
    protected function getTaskContext($context = null)
    {
        if (!$context) {
            $context = [];
        }

        if (empty($context['name'])) {
            $context['name'] = $this->getTaskName();
        }

        return parent::getTaskContext($context);
    }
}

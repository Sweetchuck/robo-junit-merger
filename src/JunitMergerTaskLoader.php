<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\JunitMerger;

use Sweetchuck\Robo\JunitMerger\Task\JunitMergerTask;

/**
 * @see \Robo\TaskAccessor
 */
trait JunitMergerTaskLoader
{
    /**
     * @return \Sweetchuck\Robo\JunitMerger\Task\JunitMergerTask|\Robo\Collection\CollectionBuilder
     */
    protected function taskJunitMerger(array $options = [])
    {
        /** @var \Sweetchuck\Robo\JunitMerger\Task\JunitMergerTask|\Robo\Collection\CollectionBuilder $task */
        $task = $this->task(JunitMergerTask::class);
        $task->setOptions($options);

        return $task;
    }
}

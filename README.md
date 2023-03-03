# Robo task to merge JUnit XML files

[![CircleCI](https://circleci.com/gh/Sweetchuck/robo-junit-merger/tree/2.x.svg?style=svg)](https://circleci.com/gh/Sweetchuck/robo-junit-merger/?branch=2.x)
[![codecov](https://codecov.io/gh/Sweetchuck/robo-junit-merger/branch/2.x/graph/badge.svg?token=HSF16OGPyr)](https://app.codecov.io/gh/Sweetchuck/robo-junit-merger/branch/2.x)


## Install

`composer require --dev sweetchuck/robo-junit-merger`


## Task - taskJunitMerge

```php
<?php

declare(strict_types = 1);

use Robo\Tasks;
use Robo\State\Data as RoboStateData;
use Sweetchuck\JunitMerger\JunitMergerDomRead;
use Sweetchuck\JunitMerger\JunitMergerDomReadWrite;
use Sweetchuck\JunitMerger\JunitMergerSubstr;
use Sweetchuck\Robo\JunitMerger\JunitMergerTaskLoader;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Yaml\Yaml;

class RoboFileAcceptance extends Tasks
{
    use JunitMergerTaskLoader;

    /**
     * @command junit-merger:merge
     */
    public function cmdJunitMergerMergerExecute(
        array $items,
        array $options = [
            'sourceType' => 'file',
            'merger' => 'substr',
            'dstFile' => '',
        ]
    ) {
        switch ($options['merger']) {
            case 'dom_read':
                $merger = new JunitMergerDomRead();
                break;

            case 'dom_read_write':
                $merger = new JunitMergerDomReadWrite();
                break;

            default:
                $merger = new JunitMergerSubstr();
                break;
        }

        $args = [
            'sourceType' => $options['sourceType'],
            'items' => new \ArrayIterator($items),
            'junitMerger' => $merger,
            'writer' => $options['dstFile'] ?
                new StreamOutput(fopen($options['dstFile'], 'w+'))
                : null,
        ];

        return $this
            ->collectionBuilder()
            ->addTask($this->taskJunitMerger($args))
            ->addCode($this->getTaskDumpAssets());
    }

    protected function getTaskDumpAssets(): \Closure
    {
        return function (RoboStateData $data): int {
            $assets = $data->getData();
            unset($assets['time']);
            $this->output()->write(Yaml::dump($assets, 99, 2, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK));

            return 0;
        };
    }
}
```

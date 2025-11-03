# Robo task to merge JUnit XML files

[![CircleCI](https://circleci.com/gh/Sweetchuck/robo-junit-merger/tree/4.x.svg?style=svg)](https://app.circleci.com/pipelines/github/Sweetchuck/robo-junit-merger?branch=4.x)
[![codecov](https://codecov.io/gh/Sweetchuck/robo-junit-merger/branch/4.x/graph/badge.svg?token=UFGGAC9Y11)](https://app.codecov.io/gh/Sweetchuck/robo-junit-merger/tree/4.x)


## Overview

When running tests in parallel across multiple processes or machines,
each test execution generates its own JUnit XML result file.
This package consolidates all these separate JUnit XML files into a single aggregated report.

**Problem it solves:**
- Combines test results from parallel test executions into one unified report
- Aggregates test metrics (assertions, errors, failures, skipped tests, execution time)
- Enables centralized test reporting and analysis in CI/CD pipelines
- Works seamlessly with Robo task runner for build automation

**Perfect for:**
- CI/CD pipelines running tests in parallel (e.g., CircleCI, GitHub Actions)
- Multi-suite test environments that need unified reporting
- Build systems that consolidate test artifacts from multiple sources


## Features

- **Multiple merger algorithms** - Support for DOM-based parsing and substring-based merging strategies
- **Flexible source handling** - Process files or entire directories
- **Automatic metric aggregation** - Combines test counts, assertions, errors, failures, skipped tests, and execution time
- **Output flexibility** - Write merged results to files or streams


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


### Task - taskJunitMerge - Inputs

**a.xml**
```xml
<?xml version="1.0" encoding="UTF-8"?>
<testsuites>
    <testsuite name="A" file="/a.php" tests="1" assertions="9" errors="0" warnings="0" failures="0" skipped="0" time="10">
        <testcase name="testA" class="A" classname="A" file="/a.php" line="8" assertions="9" time="10" />
    </testsuite>
</testsuites>
```

**b.xml**
```xml
<?xml version="1.0" encoding="UTF-8"?>
<testsuites>
    <testsuite name="B" file="/b.php" tests="1" assertions="11" errors="0" warnings="0" failures="0" skipped="0" time="14">
        <testcase name="testB" class="B" classname="B" file="/b.php" line="13" assertions="11" time="14" />
    </testsuite>
</testsuites>
```

### Task - taskJunitMerge - Output

```xml
<?xml version="1.0" encoding="UTF-8"?>
<testsuites tests="2" assertions="20" errors="0" warnings="0" failures="0" skipped="0" time="24">
  <testsuite name="A" file="/a.php" tests="1" assertions="9" errors="0" warnings="0" failures="0" skipped="0" time="10">
        <testcase name="testA" class="A" classname="A" file="/a.php" line="8" assertions="9" time="10"/>
    </testsuite>
  <testsuite name="B" file="/b.php" tests="1" assertions="11" errors="0" warnings="0" failures="0" skipped="0" time="14">
        <testcase name="testB" class="B" classname="B" file="/b.php" line="13" assertions="11" time="14"/>
    </testsuite>
</testsuites>
```

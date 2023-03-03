<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\JunitMerger\Test\Unit\Task;

/**
 * @covers \Sweetchuck\Robo\JunitMerger\Task\JunitMergerTask
 * @covers \Sweetchuck\Robo\JunitMerger\Task\BaseTask
 * @covers \Sweetchuck\Robo\JunitMerger\JunitMergerTaskLoader
 */
class JunitMergerTaskTest extends TaskTestBase
{

    protected function initTask(): static
    {
        $this->task = $this->taskBuilder->taskJunitMerger();

        return $this;
    }

    public function casesRunSuccess(): array
    {
        $fixturesDir = codecept_data_dir('fixtures');

        $mergedEmpty = implode("\n", [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<testsuites>',
            '</testsuites>',
            '',
        ]);

        $aName = "$fixturesDir/junit-01/a.xml";
        $aContent = file_get_contents($aName);

        $bName = "$fixturesDir/junit-01/b.xml";
        $bContent = file_get_contents($bName);

        return [
            'empty' => [
                [
                    'assets' => [
                        'junitMerger.merged' => $mergedEmpty,
                    ],
                ],
                [
                    'sourceType' => 'file',
                    'items' => new \ArrayIterator([]),
                ],
            ],
            'substr.file' => [
                [
                    'assets' => [
                        'junitMerger.merged' => file_get_contents("$fixturesDir/junit-01.substr.xml"),
                    ],
                ],
                [
                    'sourceType' => 'file',
                    'items' => new \ArrayIterator([$aName, $bName]),
                ],
            ],
            'substr.string' => [
                [
                    'assets' => [
                        'junitMerger.merged' => file_get_contents("$fixturesDir/junit-01.substr.xml"),
                    ],
                ],
                [
                    'sourceType' => 'string',
                    'items' => new \ArrayIterator([$aContent, $bContent]),
                ],
            ],
        ];
    }

    /**
     * @dataProvider casesRunSuccess
     */
    public function testRunSuccess(array $expected, array $options): void
    {
        $this->task->setOptions($options);
        $result = $this->task->run();

        if (array_key_exists('assets', $expected)) {
            $assets = $result->getData();
            foreach ($expected['assets'] as $assetName => $assetValue) {
                $this->tester->assertArrayHasKey($assetName, $assets);
                $this->tester->assertSame($assetValue, $assets[$assetName], "asset.$assetName");
            }
        }
    }
}

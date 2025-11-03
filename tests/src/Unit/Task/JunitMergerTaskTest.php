<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\JunitMerger\Tests\Unit\Task;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Sweetchuck\Robo\JunitMerger\JunitMergerTaskLoader;
use Sweetchuck\Robo\JunitMerger\Task\BaseTask;
use Sweetchuck\Robo\JunitMerger\Task\JunitMergerTask;

#[CoversClass(JunitMergerTask::class)]
#[CoversClass(BaseTask::class)]
#[CoversTrait(JunitMergerTaskLoader::class)]
class JunitMergerTaskTest extends TaskTestBase
{

    protected function initTask(): static
    {
        // @phpstan-ignore-next-line
        $this->task = $this->taskBuilder->taskJunitMerger();

        return $this;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function casesRunSuccess(): array
    {
        $fixturesDir = static::getFixturesDir();

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
                'expected' => [
                    'assets' => [
                        'junitMerger.merged' => $mergedEmpty,
                    ],
                ],
                'options' => [
                    'sourceType' => 'file',
                    'items' => new \ArrayIterator([]),
                ],
            ],
            'substr.file' => [
                'expected' => [
                    'assets' => [
                        'junitMerger.merged' => file_get_contents("$fixturesDir/junit-01.substr.xml"),
                    ],
                ],
                'options' => [
                    'sourceType' => 'file',
                    'items' => new \ArrayIterator([$aName, $bName]),
                ],
            ],
            'substr.string' => [
                'expected' => [
                    'assets' => [
                        'junitMerger.merged' => file_get_contents("$fixturesDir/junit-01.substr.xml"),
                    ],
                ],
                'options' => [
                    'sourceType' => 'string',
                    'items' => new \ArrayIterator([$aContent, $bContent]),
                ],
            ],
        ];
    }

    /**
     * @param array<string, mixed> $expected
     * @param array<string, mixed> $options
     */
    #[DataProvider('casesRunSuccess')]
    #[Test]
    public function testRunSuccess(array $expected, array $options): void
    {
        $this->task->setOptions($options);
        $result = $this->task->run();

        if (array_key_exists('assets', $expected)) {
            $assets = $result->getData();
            foreach ($expected['assets'] as $assetName => $assetValue) {
                static::assertArrayHasKey($assetName, $assets);
                static::assertSame($assetValue, $assets[$assetName], "asset.$assetName");
            }
        }
    }
}

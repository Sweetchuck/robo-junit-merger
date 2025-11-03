<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\JunitMerger\Tests\Acceptance\Task;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Sweetchuck\Robo\JunitMerger\JunitMergerTaskLoader;
use Sweetchuck\Robo\JunitMerger\Task\BaseTask;
use Sweetchuck\Robo\JunitMerger\Task\JunitMergerTask;
use Sweetchuck\Robo\JunitMerger\Tests\AcceptanceTester;
use Sweetchuck\Robo\JunitMerger\Tests\Helper\RoboFiles\RoboFileAcceptance;
use Symfony\Component\Yaml\Yaml;

#[CoversClass(JunitMergerTask::class)]
#[CoversClass(BaseTask::class)]
#[CoversTrait(JunitMergerTaskLoader::class)]
class JunitMergerTaskTest extends TestBase
{
    /**
     * @return array<string, mixed>
     */
    public static function casesJunitMergerMerge(): array
    {
        $fixturesDir = static::getFixturesDir();

        $mergedEmpty = implode("\n", [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<testsuites>',
            '</testsuites>',
            '',
        ]);

        $aName = "$fixturesDir/junit-01/a.xml";
        $bName = "$fixturesDir/junit-01/b.xml";

        return [
            'substr:empty' => [
                'expected' => [
                    'exitCode' => 0,
                    'stdOutput' => " [JUnit merger] is working\n" . Yaml::dump(
                        ['junitMerger.merged' => $mergedEmpty],
                        99,
                        2,
                        Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK,
                    ),
                ],
                'args' => [],
            ],
            'substr:a+b' => [
                'expected' => [
                    'exitCode' => 0,
                    'stdOutput' => " [JUnit merger] is working\n" . Yaml::dump(
                        ['junitMerger.merged' => file_get_contents("$fixturesDir/junit-01.substr.xml")],
                        99,
                        2,
                        Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK,
                    ),
                ],
                'args' => [
                    $aName,
                    $bName,
                ],
            ],
            'dom_read:a+b' => [
                'expected' => [
                    'exitCode' => 0,
                    'stdOutput' => file_get_contents("$fixturesDir/junit-01.dom_read.txt"),
                ],
                'args' => [
                    '--merger=dom_read',
                    $aName,
                    $bName,
                ],
            ],
            'dom_read_write:a+b' => [
                'expected' => [
                    'exitCode' => 0,
                    'stdOutput' => file_get_contents("$fixturesDir/junit-01.dom_read_write.txt"),
                ],
                'args' => [
                    '--merger=dom_read_write',
                    $aName,
                    $bName,
                ],
            ],
        ];
    }

    /**
     * @param array<string, mixed> $expected
     * @param array<string> $args
     */
    #[DataProvider('casesJunitMergerMerge')]
    #[Test]
    public function testJunitMergerMerge(array $expected, array $args): void
    {
        $result = $this->runRoboTask(
            RoboFileAcceptance::class,
            'junit-merger:merge',
            ...$args,
        );

        static::assertSame($expected['stdOutput'], $result['output']->fetch());
        static::assertSame($expected['exitCode'], $result['exitCode']);
    }
}

<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\JunitMerger\Tests\Acceptance\Task;

use Codeception\Attribute\DataProvider;
use Codeception\Example;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use Sweetchuck\Robo\JunitMerger\JunitMergerTaskLoader;
use Sweetchuck\Robo\JunitMerger\Task\BaseTask;
use Sweetchuck\Robo\JunitMerger\Task\JunitMergerTask;
use Sweetchuck\Robo\JunitMerger\Tests\AcceptanceTester;
use Sweetchuck\Robo\JunitMerger\Tests\Helper\RoboFiles\RoboFileAcceptance;
use Symfony\Component\Yaml\Yaml;

#[CoversClass(JunitMergerTask::class)]
#[CoversClass(BaseTask::class)]
#[CoversTrait(JunitMergerTaskLoader::class)]
class JunitMergerTaskCest
{
    public static function junitMergerMergeExamples(): array
    {
        $fixturesDir = codecept_data_dir('fixtures');

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
                'id' => 'substr:empty',
                'expected' => [
                    'exitCode' => 0,
                    'stdOutput' => Yaml::dump(
                        ['junitMerger.merged' => $mergedEmpty],
                        99,
                        2,
                        Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK,
                    ),
                    'stdError' => " [JUnit merger] is working\n",
                ],
                'args' => [],
            ],
            'substr:a+b' => [
                'id' => 'substr:a+b',
                'expected' => [
                    'exitCode' => 0,
                    'stdOutput' => Yaml::dump(
                        ['junitMerger.merged' => file_get_contents("$fixturesDir/junit-01.substr.xml")],
                        99,
                        2,
                        Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK,
                    ),
                    'stdError' => " [JUnit merger] is working\n",
                ],
                'args' => [
                    $aName,
                    $bName,
                ],
            ],
        ];
    }

    #[DataProvider('junitMergerMergeExamples')]
    public function junitMergerMerge(AcceptanceTester $tester, Example $example): void
    {
        $tester->runRoboTask(
            $example['id'],
            RoboFileAcceptance::class,
            'junit-merger:merge',
            ...$example['args'],
        );

        $exitCode = $tester->getRoboTaskExitCode($example['id']);
        $stdOutput = $tester->getRoboTaskStdOutput($example['id']);
        $stdError = $tester->getRoboTaskStdError($example['id']);

        $tester->assertSame($example['expected']['exitCode'], $exitCode);
        $tester->assertSame($example['expected']['stdOutput'], $stdOutput);
        $tester->assertSame($example['expected']['stdError'], $stdError);
    }
}

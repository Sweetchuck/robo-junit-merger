<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\JunitMerger\Test\Acceptance\Task;

use Codeception\Example;
use Sweetchuck\Robo\JunitMerger\Test\AcceptanceTester;
use Sweetchuck\Robo\JunitMerger\Test\Helper\RoboFiles\RoboFileAcceptance;
use Symfony\Component\Yaml\Yaml;

/**
 * @covers \Sweetchuck\Robo\JunitMerger\Task\JunitMergerTask<extended>
 * @covers \Sweetchuck\Robo\JunitMerger\JunitMergerTaskLoader
 */
class JunitMergerTaskCest
{
    protected function junitMergerMergeExamples(): array
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

    /**
     * @dataProvider junitMergerMergeExamples
     */
    public function junitMergerMerge(AcceptanceTester $tester, Example $example)
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

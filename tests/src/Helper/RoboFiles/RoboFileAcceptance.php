<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\JunitMerger\Tests\Helper\RoboFiles;

use Consolidation\AnnotatedCommand\Attributes\Command;
use Consolidation\AnnotatedCommand\Attributes\Help;
use Robo\Contract\TaskInterface;
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
     * {@inheritdoc}
     */
    protected function output()
    {
        return $this->getContainer()->get('output');
    }

    /**
     * @phpstan-param array<string> $items
     * @phpstan-param array<string, string> $options
     */
    #[Command(name: 'junit-merger:merge')]
    #[Help(
        description: 'Merges JUnit XML files.',
    )]
    public function cmdJunitMergerMergerExecute(
        array $items,
        array $options = [
            'sourceType' => 'file',
            'merger' => 'substr',
            'dstFile' => '',
        ]
    ): TaskInterface {
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

        $fileHandler = $options['dstFile']
            ? fopen($options['dstFile'], 'w+')
            : null;
        $args = [
            'sourceType' => $options['sourceType'],
            'items' => new \ArrayIterator($items),
            'junitMerger' => $merger,
            'writer' => $fileHandler
                ? new StreamOutput($fileHandler)
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

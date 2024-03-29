<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\JunitMerger\Task;

use Sweetchuck\JunitMerger\JunitMergerInterface;
use Sweetchuck\JunitMerger\JunitMergerSubstr;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

class JunitMergerTask extends BaseTask
{

    public function setOptions(array $options): static
    {
        parent::setOptions($options);

        if (array_key_exists('junitMerger', $options)) {
            $this->setJunitMerger($options['junitMerger']);
        }

        if (array_key_exists('sourceType', $options)) {
            $this->setSourceType($options['sourceType']);
        }

        if (array_key_exists('items', $options)) {
            $this->setItems($options['items']);
        }

        if (array_key_exists('writer', $options)) {
            $this->setWriter($options['writer']);
        }

        return $this;
    }

    protected ?JunitMergerInterface $junitMerger = null;

    public function getJunitMerger(): ?JunitMergerInterface
    {
        return $this->junitMerger;
    }

    protected function getJunitMergerFallback(): JunitMergerInterface
    {
        return $this->getJunitMerger() ?: new JunitMergerSubstr();
    }

    public function setJunitMerger(?JunitMergerInterface $junitMerger): static
    {
        $this->junitMerger = $junitMerger;

        return $this;
    }

    protected string $sourceType = 'file';

    public function getSourceType(): string
    {
        return $this->sourceType;
    }

    /**
     * @param string $sourceType
     *   Allowed values: string, file.
     */
    public function setSourceType(string $sourceType): static
    {
        $this->sourceType = $sourceType;

        return $this;
    }

    protected ?\Iterator $items = null;

    public function getItems(): ?\Iterator
    {
        return $this->items;
    }

    public function getItemsFallback(): \Iterator
    {
        return $this->getItems() ?: new \ArrayIterator([]);
    }

    public function setItems(?\Iterator $items): static
    {
        $this->items = $items;

        return $this;
    }

    protected ?OutputInterface $writer = null;

    public function getWriter(): ?OutputInterface
    {
        return $this->writer;
    }

    protected function getWriterFallback(): OutputInterface
    {
        return $this->getWriter() ?: new BufferedOutput();
    }

    public function setWriter(?OutputInterface $writer): static
    {
        $this->writer = $writer;

        return $this;
    }

    protected function runDoIt(): static
    {
        $items = $this->getItemsFallback();
        $merger = $this->getJunitMergerFallback();
        $writer = $this->getWriterFallback();
        $this->getSourceType() === 'file' ?
            $merger->mergeXmlFiles($items, $writer)
            : $merger->mergeXmlStrings($items, $writer);

        if ($writer instanceof BufferedOutput) {
            $this->assets['junitMerger.merged'] = $writer->fetch();
        }

        return $this;
    }
}

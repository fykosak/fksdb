<?php

namespace FKSDB\Models\Utils;

use Nette\InvalidStateException;
use Nette\SmartObject;

class CSVParser implements \Iterator
{
    use SmartObject;

    public const INDEX_NUMERIC = 0;
    public const INDEX_FROM_HEADER = 1;
    public const BOM = '\xEF\xBB\xBF';
    /** @var resource */
    private $file;
    private string $delimiter;
    private int $indexType;
    private ?int $rowNumber = null;
    private ?array $currentRow = null;
    private ?array $header;

    public function __construct(string $filename, int $indexType = self::INDEX_NUMERIC, string $delimiter = ';')
    {
        $this->indexType = $indexType;
        $this->delimiter = $delimiter;
        $this->file = fopen($filename, 'r');
        if (!$this->file) {
            throw new InvalidStateException("The file '" . $filename . "' cannot be read.");
        }
    }

    public function current(): array
    {
        return $this->currentRow;
    }

    public function key(): ?int
    {
        return $this->rowNumber;
    }

    public function rewind(): void
    {
        rewind($this->file);
        $this->rowNumber = 0;
        if ($this->indexType == self::INDEX_FROM_HEADER) {
            $this->header = fgetcsv($this->file, 0, $this->delimiter);
            $first = reset($this->header);
            if ($first !== false) {
                $first = preg_replace('/' . self::BOM . '/', '', $first);
                $this->header[0] = $first;
            }
        }
        if ($this->valid()) {
            $this->next();
        }
    }

    public function valid(): bool
    {
        $eof = feof($this->file);
        if ($eof) {
            fclose($this->file);
        }
        return !$eof;
    }

    public function next(): void
    {
        $this->currentRow = fgetcsv($this->file, 0, $this->delimiter);
        if ($this->indexType == self::INDEX_FROM_HEADER) {
            $result = [];
            foreach ($this->header as $i => $name) {
                $result[$name] = $this->currentRow[$i];
            }
            $this->currentRow = $result;
        }
        $this->rowNumber++;
    }
}

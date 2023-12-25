<?php

declare(strict_types=1);

namespace FKSDB\Models\Utils;

use Nette\InvalidStateException;
use Nette\SmartObject;

/**
 * @phpstan-template TRow of array
 * @phpstan-implements \Iterator<TRow>
 */
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
    /** @phpstan-var TRow|null */
    private ?array $currentRow = null;
    /** @phpstan-var array<string,string> */
    private ?array $header;

    public function __construct(string $filename, int $indexType = self::INDEX_NUMERIC, string $delimiter = ';')
    {
        $this->indexType = $indexType;
        $this->delimiter = $delimiter;
        $this->file = fopen($filename, 'r');//@phpstan-ignore-line
        if (!$this->file) {
            throw new InvalidStateException(sprintf(_('The file %s cannot be read.'), $filename));
        }
    }

    /**
     * @phpstan-return TRow
     */
    public function current(): array
    {
        return $this->currentRow;
    }

    public function key(): ?int
    {
        return $this->rowNumber;
    }

    public function next(): void
    {
        $newRow = fgetcsv($this->file, 0, $this->delimiter);
        if (!$newRow) {
            return;
        }
        $this->currentRow = $newRow;
        if ($this->indexType == self::INDEX_FROM_HEADER) {
            $result = [];
            foreach ($this->header as $i => $name) {
                $result[$name] = $this->currentRow[$i];
            }
            $this->currentRow = $result;
        }
        $this->rowNumber++;
    }

    public function rewind(): void
    {
        rewind($this->file);
        $this->rowNumber = 0;
        if ($this->indexType == self::INDEX_FROM_HEADER) {
            $this->header = fgetcsv($this->file, 0, $this->delimiter);//@phpstan-ignore-line
            $first = reset($this->header);//@phpstan-ignore-line
            if ($first !== false) {
                $first = preg_replace('/' . self::BOM . '/', '', $first);
                $this->header[0] = $first;//@phpstan-ignore-line
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
}

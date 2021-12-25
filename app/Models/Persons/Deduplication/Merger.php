<?php

namespace FKSDB\Models\Persons\Deduplication;

use Fykosak\Utils\Logging\DevNullLogger;
use Fykosak\Utils\Logging\Logger;
use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;
use Nette\MemberAccessException;

/**
 * @todo refactor to ConflictResolver, TableMergerFactory
 */
class Merger
{

    public const IDX_TRUNK = 'trunk';
    public const IDX_MERGED = 'merged';
    public const IDX_RESOLUTION = 'resolution';
    private array $conflicts = [];
    private ActiveRow $trunkRow;
    private ActiveRow $mergedRow;

    private Explorer $explorer;
    private array $configuration;
    private Logger $logger;
    /** @var TableMerger[] */
    private array $tableMergers = [];

    public function __construct(array $configuration, Explorer $explorer)
    {
        $this->configuration = $configuration;
        $this->explorer = $explorer;
        $this->logger = new DevNullLogger();
    }

    public function getLogger(): Logger
    {
        return $this->logger;
    }

    public function setLogger(Logger $logger): void
    {
        $this->logger = $logger;
    }

    public function setMergedPair(ActiveRow $trunkRow, ActiveRow $mergedRow): void
    {
        $this->trunkRow = $trunkRow;
        $this->mergedRow = $mergedRow;
    }

    public function getConflicts(): array
    {
        return $this->conflicts;
    }

    /**
     * Form values with proper resoluted values.
     */
    public function setConflictResolution(iterable $rawValues): void
    {
        foreach ($rawValues as $table => $pairs) {
            foreach ($pairs as $pairId => $values) {
                $data = &$this->getPairDataById($table, $pairId);
                foreach ($values as $column => $value) {
                    if (!isset($data[self::IDX_RESOLUTION])) {
                        $data[self::IDX_RESOLUTION] = [];
                    }
                    $data[self::IDX_RESOLUTION][$column] = $value;
                }
            }
        }
    }

    public function merge(?bool $commit = null): bool
    {
        // This workaround fixes inproper caching of referenced tables.

        $table = $this->trunkRow->getTable()->getName();
        $tableMerger = $this->getMerger($table);
        $commit = is_null($commit) ? $this->configuration['commit'] : $commit;

        $this->explorer->getConnection()->beginTransaction();

        $tableMerger->setMergedPair($this->trunkRow, $this->mergedRow);
        $this->resetConflicts();
        try {
            $tableMerger->merge();
        } catch (MemberAccessException $exception) { // this is workaround for non-working Nette database cache
            $this->explorer->getConnection()->rollBack();
            return false;
        }
        if ($this->hasConflicts()) {
            $this->explorer->getConnection()->rollBack();
            return false;
        } else {
            if ($commit) {
                $this->explorer->getConnection()->commit();
            } else {
                $this->explorer->getConnection()->rollBack();
            }
            return true;
        }
    }

    /**
     * @internal Friend of Merger class.
     */
    public function getMerger(string $table): TableMerger
    {
        if (!isset($this->tableMergers[$table])) {
            $this->tableMergers[$table] = $this->createTableMerger($table);
        }
        return $this->tableMergers[$table];
    }

    private function createTableMerger(string $table): TableMerger
    {
        $tableMerger = new TableMerger($table, $this, $this->explorer, $this->configuration['defaultStrategy'], $this->getLogger());
        if (isset($this->configuration['secondaryKeys'][$table])) {
            $tableMerger->setSecondaryKey($this->configuration['secondaryKeys'][$table]);
        }
        if (isset($this->configuration['mergeStrategies'][$table])) {
            foreach ($this->configuration['mergeStrategies'][$table] as $column => $strategy) {
                $tableMerger->setColumnMergeStrategy($column, $strategy);
            }
        }
        return $tableMerger;
    }

    private function resetConflicts(): void
    {
        foreach ($this->conflicts as $table => &$conflictPairs) {
            foreach ($conflictPairs as $pairId => &$data) {
                unset($data[self::IDX_TRUNK]);
                unset($data[self::IDX_MERGED]);
                // we keep possible resolutions
            }
        }
    }

    private function hasConflicts(): bool
    {
        foreach ($this->conflicts as $table => $conflictPairs) {
            foreach ($conflictPairs as $pairId => $data) {
                if (array_key_exists(self::IDX_TRUNK, $data)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @internal Friend of Merger class.
     */
    public function addConflict(ActiveRow $trunkRow, ActiveRow $mergedRow, string $column): void
    {
        $data = &$this->getPairData($trunkRow, $mergedRow);
        $data[self::IDX_TRUNK][$column] = $trunkRow[$column];
        $data[self::IDX_MERGED][$column] = $mergedRow[$column];
    }

    /**
     * @internal Friend of Merger class.
     */
    public function hasResolution(ActiveRow $trunkRow, ActiveRow $mergedRow, string $column): bool
    {
        $data = $this->getPairData($trunkRow, $mergedRow);
        return array_key_exists(self::IDX_RESOLUTION, $data) && array_key_exists($column, $data[self::IDX_RESOLUTION]);
    }

    /**
     * @return mixed
     * @internal Friend of Merger class.
     */
    public function getResolution(ActiveRow $trunkRow, ActiveRow $mergedRow, string $column)
    {
        $data = $this->getPairData($trunkRow, $mergedRow);
        return $data[self::IDX_RESOLUTION][$column];
    }

    private function getPairId(ActiveRow $trunkRow, ActiveRow $mergedRow): string
    {
        return $trunkRow->getPrimary() . '_' . $mergedRow->getPrimary();
    }

    private function &getPairData(ActiveRow $trunkRow, ActiveRow $mergedRow): array
    {
        $table = $trunkRow->getTable()->getName();
        $pairId = $this->getPairId($trunkRow, $mergedRow);

        return $this->getPairDataById($table, $pairId);
    }

    private function &getPairDataById(string $table, string $pairId): array
    {
        if (!isset($this->conflicts[$table])) {
            $this->conflicts[$table] = [];
        }

        if (!isset($this->conflicts[$table][$pairId])) {
            $this->conflicts[$table][$pairId] = [];
        }

        return $this->conflicts[$table][$pairId];
    }
}

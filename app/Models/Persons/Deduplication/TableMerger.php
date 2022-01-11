<?php

namespace FKSDB\Models\Persons\Deduplication;

use Fykosak\Utils\Logging\Logger;
use Fykosak\Utils\Logging\Message;
use FKSDB\Models\Persons\Deduplication\MergeStrategy\CannotMergeException;
use FKSDB\Models\Persons\Deduplication\MergeStrategy\MergeStrategy;
use Nette\Database\Conventions\AmbiguousReferenceKeyException;
use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;
use Nette\InvalidStateException;

/**
 * @note Works with single column primary keys only.
 * @note Assumes name of the FK column is the same like the referenced PK column.
 */
class TableMerger {

    private string $table;
    private Merger $merger;
    private Explorer $explorer;
    private ActiveRow $trunkRow;
    private ActiveRow $mergedRow;
    /** @var MergeStrategy[] */
    private array $columnMergeStrategies = [];
    private MergeStrategy $globalMergeStrategy;
    private Logger $logger;

    public function __construct(string $table, Merger $merger, Explorer $explorer, MergeStrategy $globalMergeStrategy, Logger $logger) {
        $this->table = $table;
        $this->merger = $merger;
        $this->explorer = $explorer;
        $this->globalMergeStrategy = $globalMergeStrategy;
        $this->logger = $logger;
    }

    /*     * ******************************
     * Merging
     * ****************************** */

    public function setMergedPair(ActiveRow $trunkRow, ActiveRow $mergedRow): void {
        $this->trunkRow = $trunkRow;
        $this->mergedRow = $mergedRow;
    }

    public function setColumnMergeStrategy(string $column, ?MergeStrategy $mergeStrategy = null): void {
        if (!$mergeStrategy) {
            unset($this->columnMergeStrategies[$column]);
        } else {
            $this->columnMergeStrategies[$column] = $mergeStrategy;
        }
    }

    private function tryColumnMerge(string $column): bool {
        if ($this->getMerger()->hasResolution($this->trunkRow, $this->mergedRow, $column)) {
            $values = [
                $column => $this->getMerger()->getResolution($this->trunkRow, $this->mergedRow, $column),
            ];
            $this->logUpdate($this->trunkRow, $values);
            $this->trunkRow->update($values);
            return true;
        } else {
            $strategy = $this->columnMergeStrategies[$column] ?? $this->globalMergeStrategy;
            try {
                $values = [
                    $column => $strategy->mergeValues($this->trunkRow[$column], $this->mergedRow[$column]),
                ];
                $this->logUpdate($this->trunkRow, $values);
                $this->trunkRow->update($values);
                return true;
            } catch (CannotMergeException $exception) {
                return false;
            }
        }
    }

    private function getMerger(): Merger {
        return $this->merger;
    }

    public function merge(?array $mergedParent = null): void {

        /*
         * We merge child-rows (referencing rows) of the merged rows.
         * We get the list of possible referencing tables from the database reflection.
         */
        foreach ($this->getReferencingTables() as $referencingTable => $fKColumn) {
            $referencingMerger = $this->getMerger()->getMerger($referencingTable);

            $trunkDependants = $this->trunkRow->related($referencingTable);
            $mergedDependants = $this->mergedRow->related($referencingTable);

            $newParent = [$fKColumn => $this->trunkRow->getPrimary()];
            /*
             * If simply changing the parent would violate some constraints (i.e. parent
             * can have only one child with certain properties -- that's the secondary key),
             * we have to recursively merge the children with the same secondary key.
             */
            if ($referencingMerger->getSecondaryKey()) {
                /* Group by ignores the FK column value, as it's being changed. */
                $groupedTrunks = $referencingMerger->groupBySecondaryKey($trunkDependants, $fKColumn);
                $groupedMerged = $referencingMerger->groupBySecondaryKey($mergedDependants, $fKColumn);
                $secondaryKeys = array_merge(array_keys($groupedTrunks), array_keys($groupedMerged));
                $secondaryKeys = array_unique($secondaryKeys);
                foreach ($secondaryKeys as $secondaryKey) {
                    $refTrunk = $groupedTrunks[$secondaryKey] ?? null;
                    /** @var ActiveRow|null $refMerged */
                    $refMerged = $groupedMerged[$secondaryKey] ?? null;
                    if ($refTrunk && $refMerged) {
                        $referencingMerger->setMergedPair($refTrunk, $refMerged);
                        $referencingMerger->merge($newParent); // recursive merge
                        if ($referencingMerger->trunkRow) {
                            $referencingMerger->setMergedPair($referencingMerger->trunkRow, $referencingMerger->mergedRow);
                        }
                    } elseif ($refMerged) {
                        $this->logUpdate($refMerged, $newParent);
                        $refMerged->update($newParent); //TODO allow delete refMerged
                    }
                }
            } else {
                /* Redirect dependant to the new parent. */
                foreach ($mergedDependants as $dependant) {
                    $this->logUpdate($dependant, $newParent);
                    $dependant->update($newParent);
                }
            }
        }
        /*
         * Delete merged row.
         * Must be done prior updating trunk as there may be unique constraint.
         */
        $this->mergedRow->delete();

        /*
         * Ordinary columns of merged rows are merged.
         */
        foreach ($this->getColumns() as $column) {
            /* Primary key is not merged. */
            if ($this->isPrimaryKey($column)) {
                continue;
            }
            /* When we are merging two rows under common parent, we ignore the foreign key. */
            if ($mergedParent && isset($mergedParent[$column])) {
                /* empty */ // row will be deleted eventually
                continue;
            }

            /* For all other columns, we try to apply merging strategy. */
            if (!$this->tryColumnMerge($column)) {
                $this->getMerger()->addConflict($this->trunkRow, $this->mergedRow, $column);
            }
        }

        /* Log the overall changes. */
        $this->logDelete($this->mergedRow);
        $this->logTrunk($this->trunkRow);
    }

    private function groupBySecondaryKey(iterable $rows, string $parentColumn): array {
        $result = [];
        foreach ($rows as $row) {
            $key = $this->getSecondaryKeyValue($row, $parentColumn);
            if (isset($result[$key])) {
                throw new InvalidStateException('Secondary key is not a key.');
            }
            $result[$key] = $row;
        }
        return $result;
    }

    private function getSecondaryKeyValue(ActiveRow $row, string $parentColumn): string {
        $key = [];
        foreach ($this->getSecondaryKey() as $column) {
            if ($column == $parentColumn) {
                continue;
            }
            $key[] = $row[$column];
        }
        return implode('_', $key);
    }

    /*     * ******************************
     * Logging sugar
     * ****************************** */

    private function logUpdate(ActiveRow $row, iterable $changes): void {
        $msg = [];
        foreach ($changes as $column => $value) {
            if ($row[$column] != $value) {
                $msg[] = "$column -> $value";
            }
        }
        if ($msg) {
            $this->logger->log(new Message(sprintf(_('%s(%s) new values: %s'), $row->getTable()->getName(), $row->getPrimary(), implode(', ', $msg)), Message::LVL_INFO));
        }
    }

    private function logDelete(ActiveRow $row): void {
        $this->logger->log(new Message(sprintf(_('%s(%s) merged and deleted.'), $row->getTable()->getName(), $row->getPrimary()), Message::LVL_INFO));
    }

    private function logTrunk(ActiveRow $row): void {
        $this->logger->log(new Message(sprintf(_('%s(%s) extended by merge.'), $row->getTable()->getName(), $row->getPrimary()), Message::LVL_INFO));
    }

    /* ******************************
     * DB reflection
     * ****************************** */

    private ?array $refTables;
    private static bool $refreshReferencing = true;

    private function getReferencingTables(): ?array {
        if (!isset($this->refTables)) {
            $this->refTables = [];
            foreach ($this->explorer->getConnection()->getDriver()->getTables() as $otherTable) {
                try {
                    [$table, $refColumn] = $this->explorer->getConventions()->getHasManyReference($this->table, $otherTable['name']);
                    self::$refreshReferencing = false;
                    $this->refTables[$table] = $refColumn;
                } catch (AmbiguousReferenceKeyException $exception) {
                    /* empty */
                }
            }
        }
        return $this->refTables;
    }

    private ?array $columns;

    private function getColumns(): ?array {
        if (!isset($this->columns)) {
            $this->columns = [];
            foreach ($this->explorer->getConnection()->getDriver()->getColumns($this->table) as $column) {
                $this->columns[] = $column['name'];
            }
        }
        return $this->columns;
    }

    private string $primaryKey;

    private function isPrimaryKey(string $column): bool {
        if (!isset($this->primaryKey)) {
            $this->primaryKey = $this->explorer->getConventions()->getPrimary($this->table);
        }
        return $column == $this->primaryKey;
    }

    private array $referencedTables = [];
    private static bool $refreshReferenced = true;

    private function getReferencedTable(string $column): string {
        if (!array_key_exists($column, $this->referencedTables)) {
            try {
                [$table, $refColumn] = $this->explorer->getConventions()->getBelongsToReference($this->table, $column);
                self::$refreshReferenced = false;
                $this->referencedTables[$column] = $table;
            } catch (\Exception $exception) {
                $this->referencedTables[$column] = null;
            }
        }
        return $this->referencedTables[$column];
    }

    private array $secondaryKey;

    private function getSecondaryKey(): ?array {
        if (!isset($this->secondaryKey)) {
            $this->secondaryKey = [];
            foreach ($this->explorer->getConnection()->getDriver()->getIndexes($this->table) as $index) {
                if ($index['unique']) {
                    $this->secondaryKey = array_merge($this->secondaryKey, $index['columns']);
                }
            }
            $this->secondaryKey = array_unique($this->secondaryKey);
        }

        return $this->secondaryKey;
    }

    public function setSecondaryKey(array $secondaryKey): void {
        $this->secondaryKey = $secondaryKey;
    }
}

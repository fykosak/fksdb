<?php

namespace FKSDB\Models\Persons\Deduplication;

use FKSDB\Models\Logging\ILogger;
use FKSDB\Models\Messages\Message;
use Nette\Database\Conventions\AmbiguousReferenceKeyException;
use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;
use Nette\InvalidStateException;
use FKSDB\Models\Persons\Deduplication\MergeStrategy\CannotMergeException;
use FKSDB\Models\Persons\Deduplication\MergeStrategy\IMergeStrategy;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @note Works with single column primary keys only.
 * @note Assumes name of the FK column is the same like the referenced PK column.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class TableMerger {

    private string $table;
    private Merger $merger;
    private Explorer $explorer;
    private ActiveRow $trunkRow;
    private ActiveRow $mergedRow;
    /** @var IMergeStrategy[] */
    private array $columnMergeStrategies = [];
    private IMergeStrategy $globalMergeStrategy;
    private ILogger $logger;

    public function __construct(string $table, Merger $merger, Explorer $explorer, IMergeStrategy $globalMergeStrategy, ILogger $logger) {
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

    public function setColumnMergeStrategy(string $column, ?IMergeStrategy $mergeStrategy = null): void {
        if (!$mergeStrategy) {
            unset($this->columnMergeStrategies[$column]);
        } else {
            $this->columnMergeStrategies[$column] = $mergeStrategy;
        }
    }

    /**
     *
     * @param mixed $column
     * @return bool
     */
    private function tryColumnMerge($column): bool {
        if ($this->getMerger()->hasResolution($this->trunkRow, $this->mergedRow, $column)) {
            $values = [
                $column => $this->getMerger()->getResolution($this->trunkRow, $this->mergedRow, $column),
            ];
            $this->logUpdate($this->trunkRow, $values);
            $this->trunkRow->update($values);
            return true;
        } else {
            if (isset($this->columnMergeStrategies[$column])) {
                $strategy = $this->columnMergeStrategies[$column];
            } else {
                $strategy = $this->globalMergeStrategy;
            }
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

    /**
     * @param null $mergedParent
     */
    public function merge($mergedParent = null): void {
        $this->trunkRow->getTable()->accessColumn(null); // stupid touch
        $this->mergedRow->getTable()->accessColumn(null); // stupid touch

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
                    $refTrunk = isset($groupedTrunks[$secondaryKey]) ? $groupedTrunks[$secondaryKey] : null;
                    /** @var ActiveRow|null $refMerged */
                    $refMerged = isset($groupedMerged[$secondaryKey]) ? $groupedMerged[$secondaryKey] : null;
                    if ($refTrunk && $refMerged) {
                        $backTrunk = $referencingMerger->trunkRow;
                        $backMerged = $referencingMerger->mergedRow;
                        $referencingMerger->setMergedPair($refTrunk, $refMerged);
                        $referencingMerger->merge($newParent); // recursive merge
                        if ($backTrunk) {
                            $referencingMerger->setMergedPair($backTrunk, $backMerged);
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

    /**
     * @param mixed $rows
     * @param mixed $parentColumn
     * @return array
     */
    private function groupBySecondaryKey($rows, $parentColumn): array {
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

    /**
     * @param ActiveRow $row
     * @param mixed $parentColumn
     * @return string
     */
    private function getSecondaryKeyValue(ActiveRow $row, $parentColumn) {
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
            $this->logger->log(new Message(sprintf(_('%s(%s) new values: %s'), $row->getTable()->getName(), $row->getPrimary(), implode(', ', $msg)), ILogger::INFO));
        }
    }

    private function logDelete(ActiveRow $row): void {
        $this->logger->log(new Message(sprintf(_('%s(%s) merged and deleted.'), $row->getTable()->getName(), $row->getPrimary()), ILogger::INFO));
    }

    private function logTrunk(ActiveRow $row): void {
        $this->logger->log(new Message(sprintf(_('%s(%s) extended by merge.'), $row->getTable()->getName(), $row->getPrimary()), ILogger::INFO));
    }

    /*     * ******************************
     * DB reflection
     * ****************************** */

    /** @var null */
    private $refTables = null;
    /** @var bool */
    private static $refreshReferencing = true;

    /**
     * @return array|null
     */
    private function getReferencingTables() {
        if ($this->refTables === null) {
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

    /** @var null */
    private $columns = null;

    /**
     * @return array|null
     */
    private function getColumns() {
        if ($this->columns === null) {
            $this->columns = [];
            foreach ($this->explorer->getConnection()->getDriver()->getColumns($this->table) as $column) {
                $this->columns[] = $column['name'];
            }
        }
        return $this->columns;
    }

    /** @var string */
    private $primaryKey;

    /**
     * @param string $column
     * @return bool
     */
    private function isPrimaryKey($column): bool {
        if ($this->primaryKey === null) {
            $this->primaryKey = $this->explorer->getConventions()->getPrimary($this->table);
        }
        return $column == $this->primaryKey;
    }

    /** @var array */
    private $referencedTables = [];
    /** @var bool */
    private static $refreshReferenced = true;

    /**
     * @param string $column
     * @return mixed
     */
    private function getReferencedTable($column) {
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

    /** @var mixed */
    private $secondaryKey;

    /**
     * @return array
     */
    private function getSecondaryKey() {
        if ($this->secondaryKey === null) {
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

    /**
     * @param string $secondaryKey
     * @return void
     */
    public function setSecondaryKey($secondaryKey): void {
        $this->secondaryKey = $secondaryKey;
    }
}

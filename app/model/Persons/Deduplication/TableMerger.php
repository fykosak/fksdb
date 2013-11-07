<?php

namespace Persons\Deduplication;

use Nette\Database\Connection;
use Nette\Database\Reflection\AmbiguousReferenceKeyException;
use Nette\Database\Reflection\MissingReferenceException;
use Nette\Database\Table\ActiveRow;
use Nette\InvalidStateException;
use PDOException;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @note Works with single column primary keys only.
 * @note Assumes name of the FK column is the same like the referenced PK column.
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class TableMerger {

    /**
     * @var string
     */
    private $table;

    /**
     * @var Merger
     */
    private $merger;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ActiveRow
     */
    private $trunkRow;

    /**
     * @var ActiveRow
     */
    private $mergedRow;

    /**
     * @var IMergeStrategy[]
     */
    private $columnMergeStrategies = array();

    /**
     * @var IMergeStrategy
     */
    private $globalMergeStrategy;

    function __construct($table, Merger $merger, Connection $connection, IMergeStrategy $globalMergeStrategy) {
        $this->table = $table;
        $this->merger = $merger;
        $this->connection = $connection;
        $this->globalMergeStrategy = $globalMergeStrategy;
    }

    /*     * ******************************
     * Merging
     * ****************************** */

    public function setMergedPair(ActiveRow $trunkRow, ActiveRow $mergedRow) {
        $this->trunkRow = $trunkRow;
        $this->mergedRow = $mergedRow;
    }

    public function setColumnMergeStrategy($column, IMergeStrategy $mergeStrategy = null) {
        if (!$mergeStrategy) {
            unset($this->columnMergeStrategies[$column]);
        } else {
            $this->columnMergeStrategies[$column] = $mergeStrategy;
        }
    }

    /**
     * @return ActiveRow
     */
    private function getTrunkRow() {
        return $this->trunkRow;
    }

    /**
     * @return ActiveRow
     */
    private function getMergedRow() {
        return $this->mergedRow;
    }

    /**
     * 
     * @param mixed $column
     * @return boolean
     */
    private function tryColumnMerge($column) {
        if ($this->getMerger()->hasResolution($this->trunkRow, $this->mergedRow, $column)) {
            $this->trunkRow->update(array(
                $column => $this->getMerger()->getResolution($this->trunkRow, $this->mergedRow, $column),
            ));
            return true;
        } else { //TODO null resolution + min date resolution   
            if (isset($this->columnMergeStrategies[$column])) {
                $strategy = $this->columnMergeStrategies[$column];
            } else {
                $strategy = $this->globalMergeStrategy;
            }
            try {
                $this->trunkRow->update(array(
                    $column => $strategy->mergeValues($this->trunkRow[$column], $this->mergedRow[$column]),
                ));
                return true;
            } catch (CannotMergeException $e) {
                return false;
            }
        }
    }

    /**
     * @return Merger
     */
    private function getMerger() {
        return $this->merger;
    }

    public function merge($newParent = null) {
        /* Merge fields */
        foreach ($this->getColumns() as $column) {
            if ($this->isPrimaryKey($column)) {
                continue;
            }
            if ($this->getReferencedTable($column)) {
                if ($newParent && isset($newParent[$column])) {
                    /* empty */ // row will be deleted eventually
                } else {
                    $this->getMergedRow()->update(array(
                        $column => $this->getTrunkRow()->$column, // set it from the trunk 
                    ));
                }
                continue;
            }
            if (!$this->tryColumnMerge($column)) {
                $this->getMerger()->addConflict($this->getTrunkRow(), $this->getMergedRow(), $column);
            }
        }
        /* Merge referenced records */
        foreach ($this->getReferencingTables() as $referencingTable => $FKcolumn) {
            $referencingMerger = $this->getMerger()->getMerger($referencingTable);

            $trunkDependants = $this->getTrunkRow()->related($referencingTable);
            $mergedDependants = $this->getMergedRow()->related($referencingTable);

            $newParent = array(
                $FKcolumn => $this->getTrunkRow()->getPrimary()
            );
            if ($referencingMerger->getSecondaryKey()) {
                // group by, but ignore the primary key
                $groupedTrunks = $referencingMerger->groupBySecondaryKey($trunkDependants, $FKcolumn);
                $groupedMerged = $referencingMerger->groupBySecondaryKey($mergedDependants, $FKcolumn);
                $secondaryKeys = array_merge(array_keys($groupedTrunks), array_keys($groupedMerged));
                foreach ($secondaryKeys as $secondaryKey) {
                    $refTrunk = isset($groupedTrunks[$secondaryKey]) ? $groupedTrunks[$secondaryKey] : null;
                    $refMerged = isset($groupedMerged[$secondaryKey]) ? $groupedMerged[$secondaryKey] : null;
                    if ($refTrunk && $refMerged) {
                        $referencingMerger->setMergedPair($refTrunk, $refMerged);
                        $referencingMerger->merge($newParent); // recursive merge
                    } else if ($refMerged) {
                        $refMerged->update($newParent); //TODO allow delete refMerged
                    }
                }
            } else {
                // redirect dependant to the new parent
                foreach ($mergedDependants as $dependant) {
                    $dependant->update($newParent);
                }
            }
        }
        /* Delete merged row */
        try {
            $this->getMergedRow()->delete();
        } catch (PDOException $e) {
            if ($e->getCode() != 23000) { //constraint violation is expected here
                throw $e;
            }
        }
    }

    private function groupBySecondaryKey($rows, $parentColumn) {
        $result = array();
        foreach ($rows as $row) {
            $key = $this->getSecondaryKeyValue($row, $parentColumn);
            if (isset($result[$key])) {
                throw new InvalidStateException('Secondary key is not a key.');
            }
            $result[$key] = $row;
        }
        return $result;
    }

    private function getSecondaryKeyValue(ActiveRow $row, $parentColumn) {
        $key = array();
        foreach ($this->getSecondaryKey() as $column) {
            if ($column == $parentColumn) {
                continue;
            }
            $key[] = $row[$column];
        }
        return implode('_', $key);
    }

    /*     * ******************************
     * DB reflection
     * ****************************** */

    private $refTables = null;

    private function getReferencingTables() {
        if ($this->refTables === null) {
            $this->refTables = array();
            foreach ($this->connection->getSupplementalDriver()->getTables() as $otherTable) {
                try {
                    list($table, $refColumn) = $this->connection->getDatabaseReflection()->getHasManyReference($this->table, $otherTable['name'], false);
                    $this->refTables[$table] = $refColumn;
                } catch (MissingReferenceException $e) {
                    /* empty */
                } catch (AmbiguousReferenceKeyException $e) {
                    /* empty */
                }
            }
        }
        return $this->refTables;
    }

    private $columns = null;

    private function getColumns() {
        if ($this->columns === null) {
            $this->columns = array();
            foreach ($this->connection->getSupplementalDriver()->getColumns($this->table) as $column) {
                $this->columns[] = $column['name'];
            }
        }
        return $this->columns;
    }

    private $primaryKey;

    private function isPrimaryKey($column) {
        if ($this->primaryKey === null) {
            $this->primaryKey = $this->connection->getDatabaseReflection()->getPrimary($this->table);
        }
        return $column == $this->primaryKey;
    }

    private $referencedTables = array();

    private function getReferencedTable($column) {
        if (!array_key_exists($column, $this->referencedTables)) {
            try {
                list($table, $refColumn) = $this->connection->getDatabaseReflection()->getBelongsToReference($this->table, $column, false);
                $this->referencedTables[$column] = $table;
            } catch (MissingReferenceException $e) {
                $this->referencedTables[$column] = null;
            }
        }
        return $this->referencedTables[$column];
    }

    private $secondaryKey;

    /**
     * @return array
     */
    private function getSecondaryKey() {
        if ($this->secondaryKey === null) {
            $this->secondaryKey = array();
            foreach ($this->connection->getSupplementalDriver()->getIndexes($this->table) as $index) {
                if ($index['unique']) {
                    $this->secondaryKey = array_merge($this->secondaryKey, $index['columns']);
                }
            }
            $this->secondaryKey = array_unique($this->secondaryKey);
        }

        return $this->secondaryKey;
    }

    public function setSecondaryKey($secondaryKey) {
        $this->secondaryKey = $secondaryKey;
    }

}

<?php

namespace Persons\Deduplication;

use FKS\Logging\DevNullLogger;
use FKS\Logging\ILogger;
use Nette\Database\Connection;
use Nette\Database\Table\ActiveRow;
use Nette\MemberAccessException;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @todo refactor to ConflictResolver, TableMergerFactory
 * @author Michal Koutný <michal@fykos.cz>
 */
class Merger {

    const IDX_TRUNK = 'trunk';
    const IDX_MERGED = 'merged';
    const IDX_RESOLUTION = 'resolution';

    private $conflicts = array();

    /**
     * @var ActiveRow
     */
    private $trunkRow;

    /**
     * @var ActiveRow
     */
    private $mergedRow;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var array
     */
    private $configuration;

    /**
     * @var ILogger
     */
    private $logger;

    /**
     *
     * @var TableMerger[]
     */
    private $tableMergers = array();

    function __construct($configuration, Connection $connection) {
        $this->configuration = $configuration;
        $this->connection = $connection;
        $this->logger = new DevNullLogger();
    }

    public function getLogger() {
        return $this->logger;
    }

    public function setLogger(ILogger $logger) {
        $this->logger = $logger;
    }

    public function setMergedPair(ActiveRow $trunkRow, ActiveRow $mergedRow) {
        $this->trunkRow = $trunkRow;
        $this->mergedRow = $mergedRow;
    }

    public function getConflicts() {
        return $this->conflicts;
    }

    /**
     * Form values with proper resoluted values.
     * 
     * @param mixed $rawValues
     */
    public function setConflictResolution($rawValues) {
        foreach ($rawValues as $table => $pairs) {
            foreach ($pairs as $pairId => $values) {
                $data = & $this->getPairDataById($table, $pairId);
                foreach ($values as $column => $value) {
                    if (!isset($data[self::IDX_RESOLUTION])) {
                        $data[self::IDX_RESOLUTION] = array();
                    }
                    $data[self::IDX_RESOLUTION][$column] = $value;
                }
            }
        }
    }

    /**
     * @param booled $commit
     * @return boolean
     */
    public function merge($commit = null) {
        $table = $this->trunkRow->getTable()->getName();
        $tableMerger = $this->getMerger($table);
        $commit = ($commit === null) ? $this->configuration['commit'] : $commit;


        $this->connection->beginTransaction();

        $tableMerger->setMergedPair($this->trunkRow, $this->mergedRow);
        $this->resetConflicts();
        try {
            $tableMerger->merge();
        } catch (MemberAccessException $e) { // this is workaround for non-working Nette database cache
            $this->connection->rollBack();
            return false;
        }
        if ($this->hasConflicts()) {
            $this->connection->rollBack();
            return false;
        } else {
            if ($commit) {
                $this->connection->commit();
            } else {
                $this->connection->rollBack();
            }
            return true;
        }
    }

    /**
     * 
     * @internal Friend of Merger class.
     * @param string $table
     * @return TableMerger
     */
    public function getMerger($table) {
        if (!isset($this->tableMergers[$table])) {
            $this->tableMergers[$table] = $this->createTableMerger($table);
        }
        return $this->tableMergers[$table];
    }

    private function createTableMerger($table) {
        $tableMerger = new TableMerger($table, $this, $this->connection, $this->configuration['defaultStrategy'], $this->getLogger());
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

    private function resetConflicts() {
        foreach ($this->conflicts as $table => &$conflictPairs) {
            foreach ($conflictPairs as $pairId => &$data) {
                unset($data[self::IDX_TRUNK]);
                unset($data[self::IDX_MERGED]);
                // we keep possible resolutions
            }
        }
    }

    private function hasConflicts() {
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
     * @param ActiveRow $trunkRow
     * @param ActiveRow $mergedRow
     * @param type $column
     */
    public function addConflict(ActiveRow $trunkRow, ActiveRow $mergedRow, $column) {
        $data = & $this->getPairData($trunkRow, $mergedRow);
        $data[self::IDX_TRUNK][$column] = $trunkRow[$column];
        $data[self::IDX_MERGED][$column] = $mergedRow[$column];
    }

    /**
     * @internal Friend of Merger class.
     * @param ActiveRow $trunkRow
     * @param ActiveRow $mergedRow
     * @param type $column
     */
    public function hasResolution(ActiveRow $trunkRow, ActiveRow $mergedRow, $column) {
        $data = $this->getPairData($trunkRow, $mergedRow);
        return array_key_exists(self::IDX_RESOLUTION, $data) && array_key_exists($column, $data[self::IDX_RESOLUTION]);
    }

    /**
     * @internal Friend of Merger class.
     * @param ActiveRow $trunkRow
     * @param ActiveRow $mergedRow
     * @param type $column
     */
    public function getResolution(ActiveRow $trunkRow, ActiveRow $mergedRow, $column) {
        $data = $this->getPairData($trunkRow, $mergedRow);
        return $data[self::IDX_RESOLUTION][$column];
    }

    private function getPairId(ActiveRow $trunkRow, ActiveRow $mergedRow) {
        return $trunkRow->getPrimary() . '_' . $mergedRow->getPrimary();
    }

    private function & getPairData(ActiveRow $trunkRow, ActiveRow $mergedRow) {
        $table = $trunkRow->getTable()->getName();
        $pairId = $this->getPairId($trunkRow, $mergedRow);

        return $this->getPairDataById($table, $pairId);
    }

    private function & getPairDataById($table, $pairId) {
        if (!isset($this->conflicts[$table])) {
            $this->conflicts[$table] = array();
        }

        if (!isset($this->conflicts[$table][$pairId])) {
            $this->conflicts[$table][$pairId] = array();
        }

        return $this->conflicts[$table][$pairId];
    }

}

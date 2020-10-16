<?php

namespace FKSDB\Persons\Deduplication;

use FKSDB\Logging\DevNullLogger;
use FKSDB\Logging\ILogger;
use Nette\Caching\Cache;
use Nette\Database\Context;
use Nette\Database\Table\ActiveRow;
use Nette\MemberAccessException;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @todo refactor to ConflictResolver, TableMergerFactory
 * @author Michal Koutný <michal@fykos.cz>
 */
class Merger {

    public const IDX_TRUNK = 'trunk';
    public const IDX_MERGED = 'merged';
    public const IDX_RESOLUTION = 'resolution';

    private array $conflicts = [];

    /** @var ActiveRow */
    private $trunkRow;

    /** @var ActiveRow */
    private $mergedRow;

    private Context $context;

    /** @var array */
    private $configuration;

    private ILogger $logger;

    /**
     *
     * @var TableMerger[]
     */
    private $tableMergers = [];

    /**
     * Merger constructor.
     * @param mixed $configuration
     * @param Context $context
     */
    public function __construct($configuration, Context $context) {
        $this->configuration = $configuration;
        $this->context = $context;
        $this->logger = new DevNullLogger();
    }

    public function getLogger(): ILogger {
        return $this->logger;
    }

    public function setLogger(ILogger $logger): void {
        $this->logger = $logger;
    }

    public function setMergedPair(ActiveRow $trunkRow, ActiveRow $mergedRow): void {
        $this->trunkRow = $trunkRow;
        $this->mergedRow = $mergedRow;
    }

    public function getConflicts(): array {
        return $this->conflicts;
    }

    /**
     * Form values with proper resoluted values.
     *
     * @param mixed $rawValues
     */
    public function setConflictResolution($rawValues): void {
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

    public function merge(?bool $commit = null): bool {
        // This workaround fixes inproper caching of referenced tables.
        $this->context->getConnection()->getCache()->clean([Cache::ALL => true]);
        $this->context->getConnection()->getDatabaseReflection()->setConnection($this->context->getConnection()); // TODO

        $table = $this->trunkRow->getTable()->getName();
        $tableMerger = $this->getMerger($table);
        $commit = is_null($commit) ? $this->configuration['commit'] : $commit;


        $this->context->getConnection()->beginTransaction();

        $tableMerger->setMergedPair($this->trunkRow, $this->mergedRow);
        $this->resetConflicts();
        try {
            $tableMerger->merge();
        } catch (MemberAccessException $exception) { // this is workaround for non-working Nette database cache
            $this->context->getConnection()->rollBack();
            return false;
        }
        if ($this->hasConflicts()) {
            $this->context->getConnection()->rollBack();
            return false;
        } else {
            if ($commit) {
                $this->context->getConnection()->commit();
            } else {
                $this->context->getConnection()->rollBack();
            }
            return true;
        }
    }

    /**
     *
     * @param string $table
     * @return TableMerger
     * @internal Friend of Merger class.
     */
    public function getMerger(string $table): TableMerger {
        if (!isset($this->tableMergers[$table])) {
            $this->tableMergers[$table] = $this->createTableMerger($table);
        }
        return $this->tableMergers[$table];
    }

    private function createTableMerger(string $table): TableMerger {
        $tableMerger = new TableMerger($table, $this, $this->context, $this->configuration['defaultStrategy'], $this->getLogger());
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

    private function resetConflicts(): void {
        foreach ($this->conflicts as $table => &$conflictPairs) {
            foreach ($conflictPairs as $pairId => &$data) {
                unset($data[self::IDX_TRUNK]);
                unset($data[self::IDX_MERGED]);
                // we keep possible resolutions
            }
        }
    }

    private function hasConflicts(): bool {
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
     * @param ActiveRow $trunkRow
     * @param ActiveRow $mergedRow
     * @param mixed $column
     * @internal Friend of Merger class.
     */
    public function addConflict(ActiveRow $trunkRow, ActiveRow $mergedRow, $column): void {
        $data = &$this->getPairData($trunkRow, $mergedRow);
        $data[self::IDX_TRUNK][$column] = $trunkRow[$column];
        $data[self::IDX_MERGED][$column] = $mergedRow[$column];
    }

    /**
     * @param ActiveRow $trunkRow
     * @param ActiveRow $mergedRow
     * @param mixed $column
     * @return bool
     * @internal Friend of Merger class.
     */
    public function hasResolution(ActiveRow $trunkRow, ActiveRow $mergedRow, $column): bool {
        $data = $this->getPairData($trunkRow, $mergedRow);
        return array_key_exists(self::IDX_RESOLUTION, $data) && array_key_exists($column, $data[self::IDX_RESOLUTION]);
    }

    /**
     * @param ActiveRow $trunkRow
     * @param ActiveRow $mergedRow
     * @param mixed $column
     * @return mixed
     * @internal Friend of Merger class.
     */
    public function getResolution(ActiveRow $trunkRow, ActiveRow $mergedRow, $column) {
        $data = $this->getPairData($trunkRow, $mergedRow);
        return $data[self::IDX_RESOLUTION][$column];
    }

    private function getPairId(ActiveRow $trunkRow, ActiveRow $mergedRow): string {
        return $trunkRow->getPrimary() . '_' . $mergedRow->getPrimary();
    }

    /**
     * @param ActiveRow $trunkRow
     * @param ActiveRow $mergedRow
     * @return mixed
     */
    private function & getPairData(ActiveRow $trunkRow, ActiveRow $mergedRow) {
        $table = $trunkRow->getTable()->getName();
        $pairId = $this->getPairId($trunkRow, $mergedRow);

        return $this->getPairDataById($table, $pairId);
    }

    /**
     * @param string $table
     * @param int $pairId
     * @return mixed
     */
    private function & getPairDataById($table, $pairId) {
        if (!isset($this->conflicts[$table])) {
            $this->conflicts[$table] = [];
        }

        if (!isset($this->conflicts[$table][$pairId])) {
            $this->conflicts[$table][$pairId] = [];
        }

        return $this->conflicts[$table][$pairId];
    }

}

<?php

namespace Exports;

use FKSDB\ORM\Models\StoredQuery\ModelStoredQuery;
use Nette\Database\Connection;
use Nette\InvalidArgumentException;
use FKSDB\Exceptions\NotImplementedException;
use Nette\Security\IResource;
use NiftyGrid\DataSource\IDataSource;

/**
 * Represents instantiotion (in term of parameters) of FKSDB\ORM\Models\StoredQuery\ModelStoredQuery. *
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class StoredQuery implements IDataSource, IResource {

    const INNER_QUERY = 'sub';

    /**
     * @var ModelStoredQuery
     */
    private $modelQuery;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var array
     */
    private $implicitParameterValues = [];

    /**
     * @var array
     */
    private $parameterValues = [];

    /**
     * @var int|null
     */
    private $count;

    /**
     * @var mixed|null
     */
    private $data;

    /**
     * @var int|null
     */
    private $limit;

    /**
     * @var int|null
     */
    private $offset;

    /**
     * @var array
     */
    private $orders = [];

    /**
     * @var array
     */
    private $parameterDefaults;

    /**
     * @var array
     */
    private $columnNames;

    /**
     * StoredQuery constructor.
     * @param ModelStoredQuery $queryPattern
     * @param Connection $connection
     */
    public function __construct(ModelStoredQuery $queryPattern, Connection $connection) {
        $this->modelQuery = $queryPattern;
        $this->connection = $connection;
    }

    public function getModelQuery(): ModelStoredQuery {
        return $this->modelQuery;
    }

    /**
     * @return StoredQueryPostProcessing|null
     */
    public function getPostProcessing() {
        return $this->getModelQuery()->getPostProcessing();
    }

    /**
     * @param array $parameters
     * @param bool $strict
     * @return void
     */
    public function setImplicitParameters(array $parameters, bool $strict = true) {
        $parameterNames = $this->getParameterNames();
        foreach ($parameters as $key => $value) {
            if ($strict && in_array($key, $parameterNames)) {
                throw new InvalidArgumentException("Implicit parameter name '$key' collides with an explicit parameter.");
            }
            if (!array_key_exists($key, $this->implicitParameterValues) || $this->implicitParameterValues[$key] != $value) {
                $this->implicitParameterValues[$key] = $value;
                $this->invalidateAll();
            }
        }
    }

    public function getImplicitParameters(): array {
        return $this->implicitParameterValues;
    }

    /**
     * @param array $parameters
     * @return void
     */
    public function setParameters(array $parameters) {
        $parameterNames = $this->getParameterNames();
        foreach ($parameters as $key => $value) {
            if (!in_array($key, $parameterNames)) {
                throw new InvalidArgumentException("Unknown parameter name '$key'.");
            }
            if (!array_key_exists($key, $this->parameterValues) || $this->parameterValues[$key] != $value) {
                $this->parameterValues[$key] = $value;
                $this->invalidateAll();
            }
        }
    }

    public function getParameters(bool $all = false): array {
        if ($all) {
            return array_merge($this->parameterDefaults, $this->parameterValues, $this->implicitParameterValues);
        } else {
            return $this->parameterValues;
        }
    }

    public function getColumnNames(): array {
        if (!$this->columnNames) {
            $this->columnNames = [];
            $innerSql = $this->getModelQuery()->sql;
            $sql = "SELECT * FROM ($innerSql) " . self::INNER_QUERY . "";

            $statement = $this->bindParams($sql);
            $statement->execute();

            $count = $statement->columnCount();

            // if ($this->connection->getSupplementalDriver()->isSupported(ISupplementalDriver::SUPPORT_COLUMNS_META)) { // workaround for PHP bugs #53782, #54695 (copy+paste from Nette\Database\Statement
            for ($col = 0; $col < $count; $col++) {
                $meta = $statement->getColumnMeta($col);
                $this->columnNames[] = $meta['name'];
            }
            // } else {
            //     $this->columnNames = range(1, $count);
            //  }
        }
        return $this->columnNames;
    }

    public function getParameterNames(): array {
        if ($this->parameterDefaults === null) {
            $this->parameterDefaults = [];
            foreach ($this->getModelQuery()->getParametersAsArray() as $parameter) {
                $this->parameterDefaults[$parameter->name] = $parameter->getDefaultValue();
            }
        }
        return array_keys($this->parameterDefaults);
    }

    private function bindParams(string $sql): \PDOStatement {
        $statement = $this->connection->getPdo()->prepare($sql);
        if ($this->getPostProcessing()) {
            $this->getPostProcessing()->resetParameters();
        }

        // bind implicit parameters
        foreach ($this->implicitParameterValues as $key => $value) {
            if ($this->getPostProcessing()) {
                $this->getPostProcessing()->bindValue($key, $value);
            }
            if (!preg_match("/:$key/", $sql)) { // this ain't foolproof
                continue;
            }
            $statement->bindValue($key, $value);
            $this->parameterValues[$key] = $value; // to propagate the implicit value of explicit parameter
        }

        // bind explicit parameters
        foreach ($this->getModelQuery()->getParametersAsArray() as $parameter) {
            $key = $parameter->name;
            if (array_key_exists($key, $this->parameterValues)) {
                $value = $this->parameterValues[$key];
            } else {
                $value = $parameter->getDefaultValue();
            }
            $type = $parameter->getPDOType();

            $statement->bindValue($key, $value, $type);
            if ($this->getPostProcessing()) {
                $this->getPostProcessing()->bindValue($key, $value);
            }
        }
        return $statement;
    }

    /**
     * @return void
     */
    private function invalidateAll() {
        $this->count = null;
        $this->data = null;
    }

    /**
     * @return void
     */
    private function invalidateData() {
        $this->data = null;
    }

    /*     * ******************************
     * Interface IDataSource
     * ****************************** */

    /**
     * @param array $filters
     * @throws NotImplementedException
     */
    public function filterData(array $filters) {
        throw new NotImplementedException();
    }

    /**
     * @param string $column
     * @return FALSE|int|mixed|null
     */
    public function getCount($column = "*") {
        if ($this->count === null) {
            $innerSql = $this->getModelQuery()->sql;
            $sql = "SELECT COUNT(1) FROM ($innerSql) " . self::INNER_QUERY;
            $statement = $this->bindParams($sql);
            $statement->execute();
            $this->count = $statement->fetchColumn();
            if ($this->getPostProcessing()) {
                if (!$this->getPostProcessing()->keepsCount()) {
                    $this->count = count($this->getData());
                }
            }
        }
        return $this->count;
    }

    /**
     * @return mixed|\PDOStatement|null
     */
    public function getData() {
        if ($this->data === null) {
            $innerSql = $this->getModelQuery()->sql;
            if ($this->orders || $this->limit !== null || $this->offset !== null) {
                $sql = "SELECT * FROM ($innerSql) " . self::INNER_QUERY;
            } else {
                $sql = $innerSql;
            }

            if ($this->orders) {
                $sql .= ' ORDER BY ' . implode(', ', $this->orders);
            }

            if ($this->limit !== null && $this->offset !== null) {
                $sql .= " LIMIT {$this->offset}, {$this->limit}";
            }

            $statement = $this->bindParams($sql);
            $statement->execute();
            $this->data = $statement;
            if ($this->getPostProcessing()) {
                $this->data = $this->getPostProcessing()->processData($this->data);
            }
        }
        return $this->data; // lazy load during iteration?
    }

    /**
     * @return null
     */
    public function getPrimaryKey() {
        return null;
        //throw new NotImplementedException();
    }

    /**
     * @param int $limit
     * @param int $offset
     */
    public function limitData($limit, $offset) {
        $this->limit = $limit;
        $this->offset = $offset;
        $this->invalidateData();
    }

    /**
     * Implemements only single column sorting.
     *
     * @param string $by column name
     * @param string $way DESC|ASC
     */
    public function orderData($by, $way) {
        if (!is_numeric($by)) {
            $by = "`$by`";
        }
        $this->orders[0] = "$by $way";
        $this->invalidateData();
    }

    public function getResourceId(): string {
        return 'export';
    }
}

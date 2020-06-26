<?php

namespace Exports;

use FKSDB\ORM\Models\StoredQuery\ModelStoredQuery;
use FKSDB\ORM\Models\StoredQuery\ModelStoredQueryParameter;
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

    /** @var ModelStoredQuery */
    private $queryPattern;
    /** @var string */
    private $qid;
    /** @var StoredQueryPostProcessing */
    private $postProcessing;
    /** @var string */
    private $sql;
    /** @var string */
    private $name;

    /** @var Connection */
    private $connection;

    /**
     * @var array
     * from Presenter
     */
    private $implicitParameterValues = [];

    /**
     * @var array
     * User setted parameters
     */
    private $parameterValues = [];
    /**
     * @var array
     * default parameter of ModelStoredQueryParameter
     */
    private $parameterDefaults = [];

    /**
     * @var int|null
     */
    private $count;

    /**
     * @var iterable|null
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
    private $columnNames;


    /**
     * StoredQuery constructor.
     * @param Connection $connection
     */
    public function __construct(Connection $connection) {
        $this->connection = $connection;
    }

    /**
     * @param ModelStoredQuery $queryPattern
     * @return void
     */
    public function setQueryPattern(ModelStoredQuery $queryPattern) {
        $this->queryPattern = $queryPattern;
        if (isset($queryPattern->name)) {
            $this->name = $queryPattern->name;
        }
        $this->sql = $this->queryPattern->sql;
        $this->queryParameters = $queryPattern->getParameters();
        $this->setPostProcessing($queryPattern->php_post_proc);
    }

    /**
     * @return ModelStoredQuery
     * @deprecated
     */
    public function getQueryPattern(): ModelStoredQuery {
        return $this->queryPattern;
    }

    public function getSQL(): string {
        return $this->sql;
    }

    /**
     * @param string $sql
     * @return void
     */
    public function setSQL(string $sql) {
        $this->sql = $sql;
    }

    /**
     * @param array $parameters
     * @param bool $strict
     * @return void
     */
    public function setContextParameters(array $parameters, bool $strict = true) {
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

    /**
     * @return StoredQueryPostProcessing|null
     */
    public function getPostProcessing() {
        return $this->postProcessing;
    }

    /**
     * @param string $className
     * @return void
     */
    public function setPostProcessing(string $className) {
        if (!class_exists($className)) {
            throw new InvalidArgumentException("Expected class name, got '$className'.");
        }
        $this->postProcessing = new $className();
    }

    /**
     * @return array
     */
    public function getImplicitParameters() {
        return $this->implicitParameterValues;
    }

    /**
     * @param $parameters
     * @return void
     */
    public function setParameters($parameters) {
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

    /**
     * @param bool $all
     * @return array
     */
    public function getParameters($all = false) {
        if ($all) {
            return array_merge($this->parameterDefaults, $this->parameterValues, $this->implicitParameterValues);
        } else {
            return $this->parameterValues;
        }
    }

    public function getName(): string {
        return $this->name ?? 'adhoc';
    }

    /**
     * @return string|null
     */
    public function getQId() {
        return $this->qid ?? ($this->hasQueryPattern() ? $this->queryPattern->qid : null);
    }

    /**
     * @param string $qid
     * @return void
     */
    public function setQId(string $qid) {
        $this->qid = $qid;
    }

    /** @var ModelStoredQueryParameter[] */
    private $queryParameters;

    /**
     * @return ModelStoredQueryParameter[]
     */
    public function getQueryParameters(): array {
        return $this->queryParameters ?? [];
    }

    /**
     * @param ModelStoredQueryParameter[] $queryParameters
     * @return void
     */
    public function setQueryParameters(array $queryParameters) {
        $this->parameterDefaults = [];
        foreach ($queryParameters as $parameter) {
            $this->parameterDefaults[$parameter->name] = $parameter->getDefaultValue();
        }
        $this->queryParameters = $queryParameters;
    }

    // return true if pattern query is real ORM model, it means is already stored in DB
    public function hasQueryPattern(): bool {
        return isset($this->queryPattern) && !is_null($this->queryPattern) && !$this->queryPattern->isNew();
    }

    public function getColumnNames(): array {
        if (!isset($this->columnNames) || is_null($this->columnNames)) {
            $this->columnNames = [];
            $innerSql = $this->getSQL();
            $sql = "SELECT * FROM ($innerSql) " . self::INNER_QUERY . "";

            $statement = $this->bindParams($sql);
            $statement->execute();

            $count = $statement->columnCount();

            for ($col = 0; $col < $count; $col++) {
                $meta = $statement->getColumnMeta($col);
                $this->columnNames[] = $meta['name'];
            }
        }
        return $this->columnNames;
    }

    public function getParameterNames(): array {
        return array_keys($this->parameterDefaults);
    }

    private function bindParams(string $sql): \PDOStatement {
        $statement = $this->connection->getPdo()->prepare($sql);
        if ($this->postProcessing) {
            $this->postProcessing->resetParameters();
        }

        // bind implicit parameters
        foreach ($this->implicitParameterValues as $key => $value) {
            if ($this->postProcessing) {
                $this->postProcessing->bindValue($key, $value);
            }
            if (!preg_match("/:$key/", $sql)) { // this ain't foolproof
                continue;
            }
            $statement->bindValue($key, $value);
            $this->parameterValues[$key] = $value; // to propagate the implicit value of explicit parameter
        }

        // bind explicit parameters
        foreach ($this->getQueryParameters() as $parameter) {
            $key = $parameter->name;
            if (isset($this->parameterValues[$key])) {
                $value = $this->parameterValues[$key];
            } else {
                $value = $parameter->getDefaultValue();
            }
            $type = $parameter->getPDOType();

            $statement->bindValue($key, $value, $type);
            if ($this->postProcessing) {
                $this->postProcessing->bindValue($key, $value);
            }
        }
        return $statement;
    }

    private function invalidateAll() {
        $this->count = null;
        $this->data = null;
    }

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
    public function getCount($column = '*') {
        if ($this->count === null) {
            $innerSql = $this->getSQL();
            $sql = "SELECT COUNT(1) FROM ($innerSql) " . self::INNER_QUERY;
            $statement = $this->bindParams($sql);
            $statement->execute();
            $this->count = $statement->fetchColumn();
            if ($this->postProcessing) {
                if (!$this->postProcessing->keepsCount()) {
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
            $innerSql = $this->getSQL();
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
            if ($this->postProcessing) {
                $this->data = $this->postProcessing->processData($this->data);
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

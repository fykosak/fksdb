<?php

namespace SQL;

use ModelStoredQuery;
use Nette\Database\Connection;
use Nette\Database\ISupplementalDriver;
use Nette\Database\Statement;
use Nette\InvalidArgumentException;
use Nette\NotImplementedException;
use Nette\Security\IResource;
use NiftyGrid\DataSource\IDataSource;

/**
 * Represents instantiotion (in term of parameters) of ModelStoredQuery. * 
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class StoredQuery implements IDataSource, IResource {

    const INNER_QUERY = 'sub';

    /**
     * @var ModelStoredQuery
     */
    private $queryPattern;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var array
     */
    private $implicitParameterValues = array();

    /**
     * @var array
     */
    private $parameterValues = array();

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
    private $orders = array();

    /**
     * @var array
     */
    private $parameterNames;

    /**
     * @var array
     */
    private $columnNames;

    function __construct(ModelStoredQuery $queryPattern, Connection $connection) {
        $this->queryPattern = $queryPattern;
        $this->connection = $connection;
    }

    public function setImplicitParameters($parameters) {
        $parameterNames = $this->getParameterNames();
        foreach ($parameters as $key => $value) {
            if (in_array($key, $parameterNames)) {
                throw new InvalidArgumentException("Implicit parameter name '$key' collides with an explicit parameter.");
            }
            if (!array_key_exists($key, $this->implicitParameterValues) || $this->implicitParameterValues[$key] != $value) {
                $this->implicitParameterValues[$key] = $value;
                $this->invalidateAll();
            }
        }
    }

    public function getImplicitParameters() {
        return $this->implicitParameterValues;
    }

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

    public function getParameters() {
        return $this->parameterValues;
    }

    public function getQueryPattern() {
        return $this->queryPattern;
    }

    public function getColumnNames() {
        if (!$this->columnNames) {
            $this->columnNames = array();
            $innerSql = $this->getQueryPattern()->sql;
            $sql = "SELECT * FROM ($innerSql) " . self::INNER_QUERY . "";

            $statement = $this->bindParams($sql);
            $statement->execute();

            $count = $statement->columnCount();

            if ($this->connection->getSupplementalDriver()->isSupported(ISupplementalDriver::SUPPORT_COLUMNS_META)) { // workaround for PHP bugs #53782, #54695 (copy+paste from Nette\Database\Statement
                for ($col = 0; $col < $count; $col++) {
                    $meta = $statement->getColumnMeta($col);
                    $this->columnNames[] = $meta['name'];
                }
            } else {
                $this->columnNames = range(1, $count);
            }
        }
        return $this->columnNames;
    }

    private function getParameterNames() {
        if ($this->parameterNames === null) {
            $this->parameterNames = array();
            foreach ($this->queryPattern->getParameters() as $parameter) {
                $this->parameterNames[] = $parameter->name;
            }
        }
        return $this->parameterNames;
    }

    /**
     * 
     * @param string $sql
     * @return Statement
     */
    private function bindParams($sql) {
        $statement = $this->connection->prepare($sql);

        // bind implicit parameters
        foreach ($this->implicitParameterValues as $key => $value) {
            if (!preg_match("/:$key/", $sql)) { // this ain't foolproof
                continue;
            }
            $statement->bindValue($key, $value);
        }

        // bind explicit parameters
        foreach ($this->getQueryPattern()->getParameters() as $parameter) {
            $key = $parameter->name;
            if (array_key_exists($key, $this->parameterValues)) {
                $value = $this->parameterValues[$key];
            } else {
                $value = $parameter->getDefaultValue();
            }
            $type = $parameter->getPDOType();

            $statement->bindValue($key, $value, $type);
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

    public function filterData(array $filters) {
        throw new NotImplementedException();
    }

    public function getCount($column = "*") {
        if ($this->count === null) {
            $innerSql = $this->getQueryPattern()->sql;
            $sql = "SELECT COUNT(1) FROM ($innerSql) " . self::INNER_QUERY;
            $statement = $this->bindParams($sql);
            $statement->execute();
            $this->count = $statement->fetchField();
        }
        return $this->count;
    }

    public function getData() {
        if ($this->data === null) {
            $innerSql = $this->getQueryPattern()->sql;
            $sql = "SELECT * FROM ($innerSql) " . self::INNER_QUERY;

            if ($this->orders) {
                $sql .= ' ORDER BY ' . implode(', ', $this->orders);
            }

            if ($this->limit !== null && $this->offset !== null) {
                $sql .= " LIMIT {$this->offset}, {$this->limit}";
            }

            $statement = $this->bindParams($sql);
            $statement->execute();
            $this->data = $statement;
        }
        return $this->data; // lazy load during iteration?
    }

    public function getPrimaryKey() {
        return null;
        //throw new NotImplementedException();
    }

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

    public function getResourceId() {
        return 'query.stored';
    }

}

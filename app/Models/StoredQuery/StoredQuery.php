<?php

namespace FKSDB\Models\StoredQuery;

use FKSDB\Models\ORM\Models\StoredQuery\ModelStoredQuery;
use FKSDB\Models\ORM\Models\StoredQuery\ModelStoredQueryParameter;
use Nette\Database\Connection;
use Nette\InvalidArgumentException;
use FKSDB\Models\Exceptions\NotImplementedException;
use Nette\Security\Resource;
use NiftyGrid\DataSource\IDataSource;

/**
 * Represents instantiotion (in term of parameters) of \FKSDB\Models\ORM\Models\StoredQuery\ModelStoredQuery. *
 */
class StoredQuery implements IDataSource, Resource {

    private const INNER_QUERY = 'sub';
    private ?ModelStoredQuery $queryPattern = null;
    private ?string $qid = null;
    private string $sql;
    private ?string $name = null;
    private array $queryParameters = [];
    private Connection $connection;
    /** from Presenter     */
    private array $implicitParameterValues = [];
    /** User set parameters     */
    private array $parameterValues = [];
    /** default parameter of ModelStoredQueryParameter     */
    private array $parameterDefaultValues = [];
    private ?int $count = null;
    private ?\PDOStatement $data = null;
    private ?int $limit = null;
    private ?int $offset = null;
    private array $orders = [];
    private ?array $columnNames = null;

    private function __construct(Connection $connection) {
        $this->connection = $connection;
    }

    public static function createFromQueryPattern(Connection $connection, ModelStoredQuery $queryPattern): self {
        $storedQuery = static::createWithoutQueryPattern($connection, $queryPattern->sql, $queryPattern->getParameters());
        $storedQuery->queryPattern = $queryPattern;
        $storedQuery->name = $queryPattern->name;
        return $storedQuery;
    }

    public static function createWithoutQueryPattern(Connection $connection, string $sql, array $parameters): self {
        $storedQuery = new StoredQuery($connection);
        $storedQuery->setSQL($sql);
        $storedQuery->setQueryParameters($parameters);
        return $storedQuery;
    }

    public function getQueryPattern(): ?ModelStoredQuery {
        return $this->queryPattern;
    }

    public function getSQL(): string {
        return $this->sql;
    }

    private function setSQL(string $sql): void {
        $this->sql = $sql;
    }

    public function setContextParameters(array $parameters, bool $strict = true): void {
        $parameterNames = $this->getParameterNames();
        foreach ($parameters as $key => $value) {
            if ($strict && in_array($key, $parameterNames)) {
                throw new InvalidArgumentException("Implicit parameter name '$key' collides with an explicit parameter.");
            }
            if (isset($this->implicitParameterValues[$key]) || $this->implicitParameterValues[$key] != $value) {
                $this->implicitParameterValues[$key] = $value;
                $this->invalidateAll();
            }
        }
    }

    public function getImplicitParameters(): array {
        return $this->implicitParameterValues;
    }

    public function setParameters(iterable $parameters): void {
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
            return array_merge($this->parameterDefaultValues, $this->parameterValues, $this->implicitParameterValues);
        } else {
            return $this->parameterValues;
        }
    }

    public function getName(): string {
        return $this->name ?? 'adhoc';
    }

    public function getQId(): ?string {
        return $this->qid ?? ($this->hasQueryPattern() ? $this->queryPattern->qid : null);
    }

    public function setQId(string $qid): void {
        $this->qid = $qid;
    }

    /**
     * @return StoredQueryParameter[]
     */
    public function getQueryParameters(): array {
        return $this->queryParameters;
    }

    private function setQueryParameters(array $queryParameters): void {
        $this->parameterDefaultValues = [];

        foreach ($queryParameters as $parameter) {
            if ($parameter instanceof ModelStoredQueryParameter) {
                $this->parameterDefaultValues[$parameter->name] = $parameter->getDefaultValue();
                $this->queryParameters[] = new StoredQueryParameter($parameter->name, $parameter->getDefaultValue(), $parameter->getPDOType());
            } elseif ($parameter instanceof StoredQueryParameter) {
                $this->parameterDefaultValues[$parameter->getName()] = $parameter->getDefaultValue();
                $this->queryParameters[] = $parameter;
            }
        }
    }

    // return true if pattern query is real ORM model, it means is already stored in DB
    public function hasQueryPattern(): bool {
        return (bool)$this->queryPattern ?? false;
    }

    public function getColumnNames(): array {
        if (!isset($this->columnNames)) {
            $this->columnNames = [];
            $innerSql = $this->getSQL();
            $sql = "SELECT * FROM ($innerSql) " . self::INNER_QUERY . '';

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
        return array_keys($this->parameterDefaultValues);
    }

    private function bindParams(string $sql): \PDOStatement {
        $statement = $this->connection->getPdo()->prepare($sql);

        // bind implicit parameters
        foreach ($this->implicitParameterValues as $key => $value) {
            if (!preg_match("/:$key/", $sql)) { // this ain't foolproof
                continue;
            }
            $statement->bindValue($key, $value);
            $this->parameterValues[$key] = $value; // to propagate the implicit value of explicit parameter
        }

        // bind explicit parameters
        foreach ($this->getQueryParameters() as $parameter) {
            $key = $parameter->getName();
            $value = $this->parameterValues[$key] ?? $parameter->getDefaultValue();
            $type = $parameter->getPDOType();

            $statement->bindValue($key, $value, $type);
        }
        return $statement;
    }

    private function invalidateAll(): void {
        $this->count = null;
        $this->data = null;
    }

    private function invalidateData(): void {
        $this->data = null;
    }

    /*     * ******************************
     * Interface IDataSource
     * ****************************** */

    /**
     * @throws NotImplementedException
     */
    public function filterData(array $filters): void {
        throw new NotImplementedException();
    }

    /**
     * @throws \PDOException
     */
    public function getCount(string $column = '*'): int {
        if (!isset($this->count)) {
            $innerSql = $this->getSQL();
            $sql = "SELECT COUNT(1) FROM ($innerSql) " . self::INNER_QUERY;
            $statement = $this->bindParams($sql);
            $statement->execute();
            $this->count = (int)$statement->fetchColumn();
        }
        return $this->count;
    }

    /**
     * @throws \PDOException
     */
    public function getData(): \PDOStatement {
        if (!isset($this->data)) {
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
        }
        return $this->data; // lazy load during iteration?
    }

    public function getPrimaryKey(): ?string {
        return null;
    }

    public function limitData(int $limit, ?int $offset = null): void {
        $this->limit = $limit;
        $this->offset = $offset;
        $this->invalidateData();
    }

    /**
     * Implements only single column sorting.
     *
     * @param string|null $by column name
     * @param string|null $way DESC|ASC
     */
    public function orderData(?string $by, ?string $way): void {
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

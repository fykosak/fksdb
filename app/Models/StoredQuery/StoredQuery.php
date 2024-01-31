<?php

declare(strict_types=1);

namespace FKSDB\Models\StoredQuery;

use FKSDB\Models\ORM\Models\StoredQuery\ParameterModel;
use FKSDB\Models\ORM\Models\StoredQuery\QueryModel;
use Nette\Database\Connection;
use Nette\InvalidArgumentException;
use Nette\Security\Resource;

/**
 * Represents instantiotion (in term of parameters) of \FKSDB\Models\ORM\Models\StoredQuery\ModelStoredQuery. *
 */
class StoredQuery implements Resource
{
    public ?QueryModel $queryPattern = null;
    private string $sql;
    /** @phpstan-var StoredQueryParameter[] */
    private array $queryParameters = [];
    private Connection $connection;
    /**
     * from Presenter
     * @phpstan-var array<string,scalar>
     */
    public array $implicitParameterValues = [];
    /**
     * User set parameters
     * @phpstan-var array<string,scalar>
     */
    private array $parameterValues = [];
    /** default parameter of ModelStoredQueryParameter
     * @phpstan-var array<string,scalar>
     */
    private array $parameterDefaultValues = [];

    private function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public static function createFromQueryPattern(Connection $connection, QueryModel $queryPattern): self
    {
        $storedQuery = static::createWithoutQueryPattern(
            $connection,
            $queryPattern->sql,
            $queryPattern->getQueryParameters()
        );
        $storedQuery->queryPattern = $queryPattern;
        return $storedQuery;
    }

    /**
     * @phpstan-param (StoredQueryParameter|ParameterModel)[] $parameters
     */
    public static function createWithoutQueryPattern(Connection $connection, string $sql, array $parameters): self
    {
        $storedQuery = new StoredQuery($connection);
        $storedQuery->setSQL($sql);
        $storedQuery->setQueryParameters($parameters);
        return $storedQuery;
    }

    private function setSQL(string $sql): void
    {
        $this->sql = $sql;
    }

    /**
     * @phpstan-param array<string,scalar> $parameters
     */
    public function setContextParameters(array $parameters, bool $strict = true): void
    {
        $parameterNames = $this->getParameterNames();
        foreach ($parameters as $key => $value) {
            if ($strict && in_array($key, $parameterNames)) {
                throw new InvalidArgumentException(
                    sprintf(_('Implicit parameter name "%s" collides with an explicit parameter.'), $key)
                );
            }
            if (isset($this->implicitParameterValues[$key]) || $this->implicitParameterValues[$key] != $value) {
                $this->implicitParameterValues[$key] = $value;
            }
        }
    }

    /**
     * @phpstan-param array<string,scalar> $parameters
     */
    public function setParameters(array $parameters): void
    {
        $parameterNames = $this->getParameterNames();
        foreach ($parameters as $key => $value) {
            if (!in_array($key, $parameterNames)) {
                throw new InvalidArgumentException(sprintf(_('Unknown parameter name "%s".'), $key));
            }
            if (!array_key_exists($key, $this->parameterValues) || $this->parameterValues[$key] != $value) {
                $this->parameterValues[$key] = $value;
            }
        }
    }

    /**
     * @phpstan-return array<string,scalar>
     */
    public function getParameters(bool $all = false): array
    {
        if ($all) {
            return array_merge($this->parameterDefaultValues, $this->parameterValues, $this->implicitParameterValues);
        } else {
            return $this->parameterValues;
        }
    }

    public function getName(): string
    {
        if (isset($this->queryPattern)) {
            return $this->queryPattern->name ?? 'adhoc';
        }
        return 'adhoc';
    }

    /**
     * @phpstan-return StoredQueryParameter[]
     */
    public function getQueryParameters(): array
    {
        return $this->queryParameters;
    }

    /**
     * @phpstan-param (StoredQueryParameter|ParameterModel)[] $queryParameters
     */
    private function setQueryParameters(array $queryParameters): void
    {
        $this->parameterDefaultValues = [];

        foreach ($queryParameters as $parameter) {
            if ($parameter instanceof ParameterModel) {
                $this->parameterDefaultValues[$parameter->name] = $parameter->getDefaultValue();
                $this->queryParameters[] = StoredQueryParameter::fromModel($parameter);
            } elseif ($parameter instanceof StoredQueryParameter) {
                $this->parameterDefaultValues[$parameter->name] = $parameter->defaultValue;
                $this->queryParameters[] = $parameter;
            }
        }
    }

    /**
     * @phpstan-return array<int,string>
     */
    public function getColumnNames(): array
    {
        static $columnNames;
        if (!isset($columnNames)) {
            $columnNames = [];
            $statement = $this->bindParams();
            $statement->execute();

            $count = $statement->columnCount();

            for ($col = 0; $col < $count; $col++) {
                $meta = $statement->getColumnMeta($col);
                $columnNames[] = $meta['name']; //@phpstan-ignore-line
            }
        }
        return $columnNames;
    }

    /**
     * @phpstan-return array<int,string>
     */
    public function getParameterNames(): array
    {
        return array_keys($this->parameterDefaultValues);
    }

    private function bindParams(): \PDOStatement
    {
        $statement = $this->connection->getPdo()->prepare($this->sql);

        // bind implicit parameters
        foreach ($this->implicitParameterValues as $key => $value) {
            if (!preg_match("/:$key/", $this->sql)) { // this ain't foolproof
                continue;
            }
            $statement->bindValue($key, $value);
            $this->parameterValues[$key] = $value; // to propagate the implicit value of explicit parameter
        }

        // bind explicit parameters
        foreach ($this->getQueryParameters() as $parameter) {
            $value = $this->parameterValues[$parameter->name] ?? $parameter->defaultValue;
            $type = $parameter->getPDOType();

            $statement->bindValue($parameter->name, $value, $type);
        }
        return $statement;
    }

    /**
     * @throws \PDOException
     */
    public function getData(): \PDOStatement
    {
        $statement = $this->bindParams();
        $statement->execute();
        return $statement;
    }

    public function getResourceId(): string
    {
        return 'export';
    }
}

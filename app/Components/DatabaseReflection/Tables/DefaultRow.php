<?php

namespace FKSDB\Components\DatabaseReflection;

/**
 * Class DefaultRow
 * @package FKSDB\Components\DatabaseReflection
 */
abstract class DefaultRow extends AbstractRow {
    /**
     * @var string
     */
    private $title;
    /**
     * @var string
     */
    private $tableName;
    /**
     * @var string
     */
    private $modelAccessKey;
    /**
     * @var string
     */
    private $description;
    /**
     * @var array
     */
    private $metaData;
    /**
     * @var int
     */
    private $permissionValue = self::PERMISSION_USE_GLOBAL_ACL;
    /**
     * @var MetaDataFactory
     */
    private $metaDataFactory;

    /**
     * StringRow constructor.
     * @param MetaDataFactory $metaDataFactory
     */
    public function __construct(MetaDataFactory $metaDataFactory) {
        $this->metaDataFactory = $metaDataFactory;
    }

    /**
     * @param string $tableName
     * @param string $title
     * @param string $modelAccessKey
     * @param array $metaData
     * @param string|null $description
     */
    final public function setUp(string $tableName, string $modelAccessKey, array $metaData, string $title, string $description = null) {
        $this->title = $title;
        $this->tableName = $tableName;
        $this->modelAccessKey = $modelAccessKey;
        $this->description = $description;
        $this->metaData = $this->metaDataFactory->getMetaData($tableName, $modelAccessKey);
    }

    /**
     * @param string $value
     * @return void
     */
    final public function setPermissionValue(string $value) {
        $this->permissionValue = constant(self::class . '::' . $value);
    }

    final public function getPermissionsValue(): int {
        return $this->permissionValue;
    }

    final public function getTitle(): string {
        return _($this->title);
    }

    final public function getDescription(): string {
        return $this->description ? _($this->description) : '';
    }

    final protected function getModelAccessKey(): string {
        return $this->modelAccessKey;
    }

    final protected function getTableName(): string {
        return $this->tableName;
    }

    final protected function getMetaData(): array {
        return $this->metaData;
    }
}

<?php

namespace FKSDB\Components\DatabaseReflection;

use Nette\Forms\Controls\BaseControl;

/**
 * Class DefaultRow
 * @author Michal Červeňák <miso@fykos.cz>
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
    /** @var bool */
    private $required = false;
    /** @var bool */
    private $omitInputField = false;
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

    final public function createField(...$args): BaseControl {
        if ($this->omitInputField) {
            throw new AbstractRowException();
        }
        $field = $this->createFormControl(...$args);
        if ($this->description) {
            $field->setOption('description', $this->getDescription());
        }
        if ($this->required) {
            $field->setRequired();
        }
        return $field;
    }

    /**
     * @param string $value
     * @return void
     */
    final public function setPermissionValue(string $value) {
        $this->permissionValue = constant(self::class . '::' . $value);
    }

    /**
     * @param bool $value
     * @return void
     */
    final public function setRequired(bool $value) {
        $this->required = $value;
    }

    /**
     * @param bool $omit
     * @return void
     */
    final public function setOmitInputField(bool $omit) {
        $this->omitInputField = $omit;
    }

    final public function getPermissionsValue(): int {
        return $this->permissionValue;
    }

    final public function getTitle(): string {
        return _($this->title);
    }

    final public function getDescription(): ?string {
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

    abstract protected function createFormControl(...$args): BaseControl;
}

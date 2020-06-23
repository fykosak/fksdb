<?php

namespace FKSDB\Components\DatabaseReflection\ColumnFactories;

use FKSDB\Components\DatabaseReflection\FieldLevelPermission;
use FKSDB\Components\DatabaseReflection\MetaDataFactory;
use FKSDB\Components\DatabaseReflection\OmittedControlException;
use Nette\Forms\Controls\BaseControl;

/**
 * Class DefaultRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class DefaultColumnFactory extends AbstractColumnFactory {
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
     * @var string|null
     */
    private $description;
    /**
     * @var array
     */
    private $metaData;
    /**
     * @var bool
     */
    private $required = false;
    /**
     * @var bool
     */
    private $omitInputField = false;
    /**
     * @var FieldLevelPermission
     */
    private $permission;
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
        $this->permission = new FieldLevelPermission(self::PERMISSION_ALLOW_ANYBODY, self::PERMISSION_ALLOW_ANYBODY);
    }

    /**
     * @param string $tableName
     * @param string $modelAccessKey
     * @param string $title
     * @param string|null $description
     * @return void
     */
    final public function setUp(string $tableName, string $modelAccessKey, string $title, $description) {
        $this->title = $title;
        $this->tableName = $tableName;
        $this->modelAccessKey = $modelAccessKey;
        $this->description = $description;
        $this->metaData = $this->metaDataFactory->getMetaData($tableName, $modelAccessKey);
    }

    /**
     * @param mixed ...$args
     * @return BaseControl
     * @throws OmittedControlException
     */
    final public function createField(...$args): BaseControl {
        if ($this->omitInputField) {
            throw new OmittedControlException();
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
     * @param array $values
     * @return void
     */
    final public function setPermissionValue(array $values) {
        $this->permission = new FieldLevelPermission(
            constant(self::class . '::PERMISSION_ALLOW_' . $values['read']),
            constant(self::class . '::PERMISSION_ALLOW_' . $values['write'])
        );

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

    final public function getPermission(): FieldLevelPermission {
        return $this->permission;
    }

    final public function getTitle(): string {
        return _($this->title);
    }

    /**
     * @return string|null
     */
    final public function getDescription() {
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

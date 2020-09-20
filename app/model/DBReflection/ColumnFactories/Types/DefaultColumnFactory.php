<?php

namespace FKSDB\DBReflection\ColumnFactories;

use FKSDB\DBReflection\FieldLevelPermission;
use FKSDB\DBReflection\MetaDataFactory;
use FKSDB\DBReflection\OmittedControlException;
use Nette\Forms\Controls\BaseControl;

/**
 * Class DefaultRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class DefaultColumnFactory extends AbstractColumnFactory {

    private string $title;

    private string $tableName;

    private string $modelAccessKey;

    private ?string $description;

    private array $metaData;

    private bool $required = false;

    private bool $omitInputField = false;

    private FieldLevelPermission $permission;

    private MetaDataFactory $metaDataFactory;

    public function __construct(MetaDataFactory $metaDataFactory) {
        $this->metaDataFactory = $metaDataFactory;
        $this->permission = new FieldLevelPermission(self::PERMISSION_ALLOW_ANYBODY, self::PERMISSION_ALLOW_ANYBODY);
    }

    final public function setUp(string $tableName, string $modelAccessKey, string $title, ?string $description): void {
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

    final public function setPermissionValue(array $values): void {
        $this->permission = new FieldLevelPermission(
            constant(self::class . '::PERMISSION_ALLOW_' . $values['read']),
            constant(self::class . '::PERMISSION_ALLOW_' . $values['write'])
        );
    }

    final public function setRequired(bool $value): void {
        $this->required = $value;
    }

    final public function setOmitInputField(bool $omit): void {
        $this->omitInputField = $omit;
    }

    final public function getPermission(): FieldLevelPermission {
        return $this->permission;
    }

    final public function getTitle(): string {
        return _($this->title);
    }

    final public function getDescription(): ?string {
        return $this->description ? _($this->description) : null;
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

    /**
     * @param mixed ...$args
     * @return BaseControl
     * @throws OmittedControlException
     */
    abstract protected function createFormControl(...$args): BaseControl;
}

<?php

namespace FKSDB\Components\DatabaseReflection;

use Nette\Forms\Controls\BaseControl;

/**
 * Class DefaultRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class DefaultRow extends AbstractRow {

    private string $title;

    private string $tableName;

    private string $modelAccessKey;

    private ?string $description;

    private array $metaData;

    private bool $required = false;

    private bool $omitInputField = false;

    private int $permissionValue = self::PERMISSION_USE_GLOBAL_ACL;

    private MetaDataFactory $metaDataFactory;

    /**
     * StringRow constructor.
     * @param MetaDataFactory $metaDataFactory
     */
    public function __construct(MetaDataFactory $metaDataFactory) {
        $this->metaDataFactory = $metaDataFactory;
    }

    final public function setUp(string $tableName, string $modelAccessKey, array $metaData, string $title, ?string $description = null): void {
        $this->title = $title;
        $this->tableName = $tableName;
        $this->modelAccessKey = $modelAccessKey;
        $this->description = $description;
        $this->metaData = $this->metaDataFactory->getMetaData($tableName, $modelAccessKey);
    }

    /**
     * @param mixed ...$args
     * @return BaseControl
     * @throws AbstractRowException
     */
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

    final public function setPermissionValue(string $value): void {
        $this->permissionValue = constant(self::class . '::' . $value);
    }

    final public function setRequired(bool $value): void {
        $this->required = $value;
    }

    final public function setOmitInputField(bool $omit): void {
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

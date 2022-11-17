<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns;

use FKSDB\Components\Badges\NotSetBadge;
use FKSDB\Components\Badges\PermissionDeniedBadge;
use FKSDB\Models\ORM\FieldLevelPermissionValue;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\MetaDataFactory;
use FKSDB\Models\ORM\OmittedControlException;
use Fykosak\NetteORM\Model;
use FKSDB\Models\ValuePrinters\StringPrinter;
use Nette\Forms\Controls\BaseControl;
use Nette\SmartObject;
use Nette\Utils\Html;

abstract class ColumnFactory
{
    use SmartObject;

    private string $title;
    private string $tableName;
    private string $modelAccessKey;
    private ?string $description;
    private array $metaData;
    private bool $required = false;
    private bool $omitInputField = false;
    private FieldLevelPermission $permission;
    private string $modelClassName;

    public function __construct(private readonly MetaDataFactory $metaDataFactory)
    {
        $this->permission = new FieldLevelPermission(
            FieldLevelPermissionValue::NoAccess,
            FieldLevelPermissionValue::NoAccess
        );
    }

    final public function setUp(
        string $tableName,
        string $modelClassName,
        string $modelAccessKey,
        string $title,
        ?string $description
    ): void {
        $this->title = $title;
        $this->tableName = $tableName;
        $this->modelAccessKey = $modelAccessKey;
        $this->description = $description;
        $this->modelClassName = $modelClassName;
    }

    /**
     * @throws OmittedControlException
     */
    final public function createField(...$args): BaseControl
    {
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

    final public function setPermissionValue(array $values): void
    {
        $this->permission = new FieldLevelPermission(
            $values['read'],
            $values['write'],
        );
    }

    final public function setRequired(bool $value): void
    {
        $this->required = $value;
    }

    final public function setOmitInputField(bool $omit): void
    {
        $this->omitInputField = $omit;
    }

    final public function getPermission(): FieldLevelPermission
    {
        return $this->permission;
    }

    final public function getTitle(): string
    {
        return _($this->title);
    }

    final public function getDescription(): ?string
    {
        return $this->description ? _($this->description) : null;
    }

    final protected function getModelAccessKey(): string
    {
        return $this->modelAccessKey;
    }

    final protected function getTableName(): string
    {
        return $this->tableName;
    }

    final protected function getMetaData(): array
    {
        if (!isset($this->metaData)) {
            $this->metaData = $this->metaDataFactory->getMetaData($this->tableName, $this->modelAccessKey);
        }
        return $this->metaData;
    }

    /**
     * @throws OmittedControlException
     */
    protected function createFormControl(...$args): BaseControl
    {
        throw new OmittedControlException();
    }

    protected function createHtmlValue(Model $model): Html
    {
        return (new StringPrinter())($model->{$this->getModelAccessKey()});
    }

    /**
     * @throws CannotAccessModelException
     * @throws \ReflectionException
     */
    final public function render(Model $originalModel, FieldLevelPermissionValue $userPermissionsLevel): Html
    {
        if (!$this->hasReadPermissions($userPermissionsLevel)) {
            return PermissionDeniedBadge::getHtml();
        }
        $preRender = $this->prerenderOriginalModel($originalModel);
        if (!is_null($preRender)) {
            return $preRender;
        }
        $model = $this->resolveModel($originalModel);
        if (is_null($model)) {
            return $this->renderNullModel();
        }
        return $this->createHtmlValue($model);
    }

    protected function prerenderOriginalModel(Model $originalModel): ?Html
    {
        return null;
    }

    /**
     * @throws CannotAccessModelException
     * @throws \ReflectionException
     */
    protected function resolveModel(Model $modelSingle): ?Model
    {
        return $modelSingle->getReferencedModel($this->modelClassName);
    }

    final public function hasReadPermissions(FieldLevelPermissionValue $userValue): bool
    {
        return $userValue->value >= $this->getPermission()->read->value;
    }

    final public function hasWritePermissions(FieldLevelPermissionValue $userValue): bool
    {
        return $userValue->value >= $this->getPermission()->write->value;
    }

    protected function renderNullModel(): Html
    {
        return NotSetBadge::getHtml();
    }
}

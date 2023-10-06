<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns;

use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\MetaDataFactory;
use FKSDB\Models\UI\NotSetBadge;
use FKSDB\Models\UI\PermissionDeniedBadge;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\Localization\GettextTranslator;
use Nette\DI\Container;
use Nette\Forms\Controls\BaseControl;
use Nette\SmartObject;
use Nette\Utils\Html;

/**
 * @phpstan-template TModel of Model
 * @phpstan-template ArgType
 * @phpstan-import-type TMetaData from MetaDataFactory
 */
abstract class ColumnFactory
{
    use SmartObject;

    public const PERMISSION_ALLOW_ANYBODY = 1;
    public const PERMISSION_ALLOW_BASIC = 16;
    public const PERMISSION_ALLOW_RESTRICT = 128;
    public const PERMISSION_ALLOW_FULL = 1024;
    protected string $title;
    protected string $tableName;
    protected string $modelAccessKey;
    protected ?string $description;
    /**
     * @phpstan-var TMetaData
     */
    protected array $metaData;
    protected bool $required = false;
    protected bool $omitInputField = false;
    protected bool $isWriteOnly = true;
    public FieldLevelPermission $permission;
    protected MetaDataFactory $metaDataFactory;
    protected GettextTranslator $translator;
    /** @phpstan-var class-string<TModel> */
    protected string $modelClassName;

    public function __construct(Container $container)
    {
        $container->callInjects($this);
    }

    final public function injectMeta(MetaDataFactory $metaDataFactory, GettextTranslator $translator): void
    {
        $this->metaDataFactory = $metaDataFactory;
        $this->translator = $translator;
    }

    /**
     * @phpstan-param class-string<TModel> $modelClassName
     */
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
     * @phpstan-param ArgType $args
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

    final public function setPermissionValue(string $value): void
    {
        $this->permission = new FieldLevelPermission(
            constant(self::class . '::PERMISSION_ALLOW_' . $value),
            constant(self::class . '::PERMISSION_ALLOW_' . $value)
        );
    }

    public function setWriteOnly(bool $isWriteOnly): void
    {
        $this->isWriteOnly = $isWriteOnly;
    }

    final public function setRequired(bool $value): void
    {
        $this->required = $value;
    }

    final public function setOmitInputField(bool $omit): void
    {
        $this->omitInputField = $omit;
    }

    final public function getTitle(): string
    {
        return _($this->title);
    }

    final public function getDescription(): ?string
    {
        return $this->description ? _($this->description) : null;
    }

    /**
     * @phpstan-return TMetaData
     */
    final protected function getMetaData(): array
    {
        if (!isset($this->metaData)) {
            $this->metaData = $this->metaDataFactory->getMetaData($this->tableName, $this->modelAccessKey);
        }
        return $this->metaData;
    }

    /**
     * @throws OmittedControlException
     * @phpstan-param ArgType $args
     */
    abstract protected function createFormControl(...$args): BaseControl;

    /**
     * @param TModel $model
     */
    abstract protected function createHtmlValue(Model $model): Html;

    /**
     * @throws CannotAccessModelException
     * @throws \ReflectionException
     */
    final public function render(Model $originalModel, int $userPermissionsLevel): Html
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
     * @phpstan-return TModel|null
     * @throws \ReflectionException
     * @throws CannotAccessModelException
     */
    protected function resolveModel(Model $modelSingle): ?Model
    {
        return $modelSingle->getReferencedModel($this->modelClassName);
    }

    final public function hasReadPermissions(int $userValue): bool
    {
        return $userValue >= $this->permission->read;
    }

    final public function hasWritePermissions(int $userValue): bool
    {
        return $userValue >= $this->permission->write;
    }

    protected function renderNullModel(): Html
    {
        return NotSetBadge::getHtml();
    }
}

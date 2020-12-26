<?php

namespace FKSDB\Models\DBReflection\ColumnFactories\Types;

use FKSDB\Components\Controls\Badges\NotSetBadge;
use FKSDB\Components\Controls\Badges\PermissionDeniedBadge;
use FKSDB\Models\DBReflection\ColumnFactories\IColumnFactory;
use FKSDB\Models\DBReflection\FieldLevelPermission;
use FKSDB\Models\DBReflection\MetaDataFactory;
use FKSDB\Models\DBReflection\OmittedControlException;
use FKSDB\Models\DBReflection\ReferencedFactory;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\AbstractModelSingle;
use FKSDB\Models\ValuePrinters\StringPrinter;
use Nette\Forms\Controls\BaseControl;
use Nette\SmartObject;
use Nette\Utils\Html;

/**
 * Class DefaultRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class DefaultColumnFactory implements IColumnFactory {

    use SmartObject;

    public const PERMISSION_ALLOW_ANYBODY = 1;
    public const PERMISSION_ALLOW_BASIC = 16;
    public const PERMISSION_ALLOW_RESTRICT = 128;
    public const PERMISSION_ALLOW_FULL = 1024;

    private string $title;

    private string $tableName;

    private string $modelAccessKey;

    private ?string $description;

    private array $metaData;

    private bool $required = false;

    private bool $omitInputField = false;

    private FieldLevelPermission $permission;

    private MetaDataFactory $metaDataFactory;

    protected ReferencedFactory $referencedFactory;

    public function __construct(MetaDataFactory $metaDataFactory) {
        $this->metaDataFactory = $metaDataFactory;
        $this->permission = new FieldLevelPermission(self::PERMISSION_ALLOW_ANYBODY, self::PERMISSION_ALLOW_ANYBODY);
    }

    final public function setUp(string $tableName, string $modelAccessKey, string $title, ?string $description): void {
        $this->title = $title;
        $this->tableName = $tableName;
        $this->modelAccessKey = $modelAccessKey;
        $this->description = $description;
    }

    public function setReferencedFactory(ReferencedFactory $factory): void {
        $this->referencedFactory = $factory;
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
        if (!isset($this->metaData)) {
            $this->metaData = $this->metaDataFactory->getMetaData($this->tableName, $this->modelAccessKey);
        }
        return $this->metaData;
    }

    /**
     * @param mixed ...$args
     * @return BaseControl
     * @throws OmittedControlException
     */
    protected function createFormControl(...$args): BaseControl {
        throw new OmittedControlException();
    }

    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return (new StringPrinter())($model->{$this->getModelAccessKey()});
    }

    /**
     * @param AbstractModelSingle $model
     * @param int $userPermissionsLevel
     * @return Html
     * @throws BadTypeException
     */
    final public function render(AbstractModelSingle $model, int $userPermissionsLevel): Html {
        if (!$this->hasReadPermissions($userPermissionsLevel)) {
            return PermissionDeniedBadge::getHtml();
        }
        $model = $this->resolveModel($model);
        if (is_null($model)) {
            return $this->renderNullModel();
        }
        return $this->createHtmlValue($model);
    }

    /**
     * @param AbstractModelSingle $modelSingleSingle
     * @return AbstractModelSingle|null
     * @throws BadTypeException
     */
    protected function resolveModel(AbstractModelSingle $modelSingleSingle): ?AbstractModelSingle {
        return $this->referencedFactory->accessModel($modelSingleSingle);
    }

    final public function hasReadPermissions(int $userValue): bool {
        return $userValue >= $this->getPermission()->read;
    }

    final public function hasWritePermissions(int $userValue): bool {
        return $userValue >= $this->getPermission()->write;
    }

    protected function renderNullModel(): Html {
        return NotSetBadge::getHtml();
    }
}

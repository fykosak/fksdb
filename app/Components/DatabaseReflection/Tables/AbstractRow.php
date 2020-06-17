<?php

namespace FKSDB\Components\DatabaseReflection;

use FKSDB\Components\Controls\Badges\NotSetBadge;
use FKSDB\Components\Controls\Badges\PermissionDeniedBadge;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Application\BadRequestException;
use Nette\DeprecatedException;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;
use Nette\SmartObject;
use Nette\Utils\Html;

/**
 * Class AbstractRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class AbstractRow {
    use SmartObject;

    const PERMISSION_USE_GLOBAL_ACL = 1;
    const PERMISSION_ALLOW_BASIC = 16;
    const PERMISSION_ALLOW_RESTRICT = 128;
    const PERMISSION_ALLOW_FULL = 1024;
    /**
     * @var string
     */
    private $modelClassName;
    /**
     * @var array
     */
    private $referencedAccess;

    /**
     * @param mixed ...$args
     * @return BaseControl
     * @throws OmittedControlException
     */
    public function createField(...$args): BaseControl {
        return new TextInput($this->getTitle());
    }

    /**
     * @return string|null
     */
    public function getDescription() {
        return null;
    }

    /**
     * @param AbstractModelSingle $model
     * @param int $userPermissionsLevel
     * @return Html
     * @throws BadRequestException
     */
    final public function renderValue(AbstractModelSingle $model, int $userPermissionsLevel): Html {
        if (!$this->hasReadPermissions($userPermissionsLevel)) {
            return PermissionDeniedBadge::getHtml();
        }
        $model = $this->getModel($model);
        if (is_null($model)) {
            return $this->createNullHtmlValue();
        }
        return $this->createHtmlValue($model);
    }

    protected function createNullHtmlValue(): Html {
        return NotSetBadge::getHtml();
    }

    /**
     * @param string $modelClassName
     * @param array $referencedAccess
     * @return void
     */
    final public function setReferencedParams(string $modelClassName, array $referencedAccess) {
        $this->modelClassName = $modelClassName;
        $this->referencedAccess = $referencedAccess;
    }

    /**
     * @param AbstractModelSingle $model
     * @return AbstractModelSingle|null
     * @throws BadRequestException
     */
    protected function getModel(AbstractModelSingle $model) {
        // if referenced access is not set return Model
        //if (!isset($this->referencedAccess) || !isset($this->modelClassName)) {
        if (!$this->referencedAccess || !$this->modelClassName) {
            return $model;
        }
        // referenced access was called at this time
        $modelClassName = $this->modelClassName;
        // model is already instance of desired model
        if ($model instanceof $modelClassName) {
            return $model;
        }
        // try interface and access via get<Model>()
        if ($model instanceof $this->referencedAccess['modelClassName']) {
            $referencedModel = $model->{$this->referencedAccess['method']}();
            if ($referencedModel) {
                if ($referencedModel instanceof $modelClassName) {
                    return $referencedModel;
                }
                throw new BadTypeException($modelClassName, $referencedModel);
            }
            return null;
        }
        throw new BadRequestException(sprintf('Can not access model %s from %s', $modelClassName, get_class($model)));
    }

    abstract protected function createHtmlValue(AbstractModelSingle $model): Html;

    final public function hasReadPermissions(int $userValue): bool {
        return $userValue >= $this->getPermission()->read;
    }

    final public function hasWritePermissions(int $userValue): bool {
        return $userValue >= $this->getPermission()->write;
    }

    public function getPermission(): FieldLevelPermission {
        return new FieldLevelPermission($this->getPermissionsValue(), $this->getPermissionsValue());
    }

    /**
     * @return int
     * @deprecated
     */
    public function getPermissionsValue(): int {
        throw new DeprecatedException();
    }

    abstract public function getTitle(): string;
}

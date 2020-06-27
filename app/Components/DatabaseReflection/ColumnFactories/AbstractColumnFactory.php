<?php

namespace FKSDB\Components\DatabaseReflection\ColumnFactories;

use FKSDB\Components\Controls\Badges\NotSetBadge;
use FKSDB\Components\Controls\Badges\PermissionDeniedBadge;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Application\BadRequestException;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;
use Nette\InvalidStateException;
use Nette\SmartObject;
use Nette\Utils\Html;

/**
 * Class AbstractRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class AbstractColumnFactory implements IColumnFactory {
    use SmartObject;

    const PERMISSION_ALLOW_ANYBODY = 1;
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

    final public function hasReadPermissions(int $userValue): bool {
        return $userValue >= $this->getPermission()->read;
    }

    final public function hasWritePermissions(int $userValue): bool {
        return $userValue >= $this->getPermission()->write;
    }

    /**
     * @param array $referencedAccess
     * @return void
     */
    final public function setReferencedAccess(array $referencedAccess) {
        $this->referencedAccess = $referencedAccess;
    }

    /**
     * @param string $modelClassName
     * @return void
     */
    final public function setModelClassName(string $modelClassName) {
        $this->modelClassName = $modelClassName;
    }

    /**
     * @param AbstractModelSingle $model
     * @return AbstractModelSingle|null
     * @throws BadRequestException
     */
    protected function getModel(AbstractModelSingle $model) {
        // model is already instance of desired model
        if ($model instanceof $this->modelClassName) {
            return $model;
        }

        // if referenced access is not set and model is not desired model throw exception
        //if (!isset($this->referencedAccess)) {
        if (!$this->referencedAccess) {
            throw new InvalidStateException();
        }
        return $this->accessReferencedModel($model);
    }

    /**
     * try interface and access via get<Model>()
     * @param AbstractModelSingle $model
     * @return AbstractModelSingle|null
     * @throws BadRequestException
     * @throws BadTypeException
     */
    protected function accessReferencedModel(AbstractModelSingle $model) {
        if ($model instanceof $this->referencedAccess['modelClassName']) {
            $referencedModel = $model->{$this->referencedAccess['method']}();
            if ($referencedModel) {
                if ($referencedModel instanceof $this->modelClassName) {
                    return $referencedModel;
                }
                throw new BadTypeException($this->modelClassName, $referencedModel);
            }
            return null;
        }
        throw new BadRequestException(sprintf('Can not access model %s from %s', $this->modelClassName, get_class($model)));
    }

    protected function createNullHtmlValue(): Html {
        return NotSetBadge::getHtml();
    }

    abstract protected function createHtmlValue(AbstractModelSingle $model): Html;
}

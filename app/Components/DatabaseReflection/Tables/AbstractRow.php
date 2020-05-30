<?php

namespace FKSDB\Components\DatabaseReflection;

use FKSDB\Components\Controls\Badges\NotSetBadge;
use FKSDB\Components\Controls\Badges\PermissionDeniedBadge;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Application\BadRequestException;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;
use Nette\SmartObject;
use Nette\Utils\Html;

/**
 * Class AbstractField
 * *
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
    private $modelClassName = null;
    /**
     * @var string[]
     */
    private $referencedAccess;

    /**
     * @param array $args
     * @return BaseControl
     * @throws AbstractRowException
     */
    public function createField(...$args): BaseControl {
        return new TextInput($this->getTitle());
    }

    /**
     * @return null|string
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
        if (!$this->hasPermissions($userPermissionsLevel)) {
            return PermissionDeniedBadge::getHtml();
        }
        $model = $this->getModel($model);
        if (is_null($model)) {
            return $this->nullModelHtmlValue();
        }
        return $this->createHtmlValue($model);
    }

    protected function nullModelHtmlValue(): Html {
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
        $modelClassName = $this->getModelClassName();
        if (!isset($this->referencedAccess) || is_null($modelClassName)) {
            return $model;
        }

        if ($model instanceof $modelClassName) {
            return $model;
        }

        if (isset($this->referencedAccess) && $model instanceof $this->referencedAccess['modelClassName']) {
            $referencedModel = $model->{$this->referencedAccess['method']}();
            if ($referencedModel) {
                return $referencedModel;
            }
            return null;
        }
        throw new BadRequestException(sprintf('Can not access model %s from %s', $modelClassName, get_class($model)));
    }

    abstract protected function createHtmlValue(AbstractModelSingle $model): Html;

    final protected function hasPermissions(int $userValue): bool {
        return $userValue >= $this->getPermissionsValue();
    }

    /**
     * @return string|null
     */
    final protected function getModelClassName() {
        return $this->modelClassName;
    }

    abstract public function getPermissionsValue(): int;

    abstract public function getTitle(): string;
}

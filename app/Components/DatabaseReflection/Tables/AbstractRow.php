<?php

namespace FKSDB\Components\DatabaseReflection;

use FKSDB\Components\Controls\Badges\NotSetBadge;
use FKSDB\Components\Controls\Badges\PermissionDeniedBadge;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Application\BadRequestException;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;
use Nette\SmartObject;
use Nette\Utils\Html;
use Tracy\Debugger;

/**
 * Class AbstractRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class AbstractRow {
    use SmartObject;

    public const PERMISSION_USE_GLOBAL_ACL = 1;
    public const PERMISSION_ALLOW_BASIC = 16;
    public const PERMISSION_ALLOW_RESTRICT = 128;
    public const PERMISSION_ALLOW_FULL = 1024;
    /**
     * @var string
     */
    private ?string $modelClassName = null;
    /**
     * @var string[]
     */
    private ?array $referencedAccess = null;

    public function createField(...$args): BaseControl {
        return new TextInput($this->getTitle());
    }

    public function getDescription(): ?string {
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

    final public function setReferencedParams(string $modelClassName, array $referencedAccess): void {
        $this->modelClassName = $modelClassName;
        $this->referencedAccess = $referencedAccess;
    }

    /**
     * @param AbstractModelSingle $model
     * @return AbstractModelSingle|null
     * @throws BadRequestException
     */
    protected function getModel(AbstractModelSingle $model): ?AbstractModelSingle {
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
                if ($referencedModel instanceof $modelClassName) {
                    return $referencedModel;
                }
                Debugger::barDump($this);
                throw new BadTypeException($modelClassName, $referencedModel);
            }
            return null;
        }

        throw new BadRequestException(sprintf('Can not access model %s from %s', $modelClassName, get_class($model)));
    }

    abstract protected function createHtmlValue(AbstractModelSingle $model): Html;

    final protected function hasPermissions(int $userValue): bool {
        return $userValue >= $this->getPermissionsValue();
    }

    final protected function getModelClassName(): ?string {
        return $this->modelClassName;
    }

    abstract public function getPermissionsValue(): int;

    abstract public function getTitle(): string;
}

<?php

namespace FKSDB\DBReflection\ColumnFactories;

use FKSDB\Components\Controls\Badges\NotSetBadge;
use FKSDB\Components\Controls\Badges\PermissionDeniedBadge;
use FKSDB\DBReflection\ReferencedFactory;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;
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
    /** @var ReferencedFactory */
    protected $referencedFactory;

    public function createField(...$args): BaseControl {
        return new TextInput($this->getTitle());
    }

    /**
     * @param ReferencedFactory $factory
     * @return void
     */
    public function setReferencedFactory(ReferencedFactory $factory) {
        $this->referencedFactory = $factory;
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
     * @param AbstractModelSingle $modelSingle
     * @return AbstractModelSingle|null
     * @throws BadTypeException
     */
    protected function resolveModel(AbstractModelSingle $modelSingle) {
        return $this->referencedFactory->accessModel($modelSingle);
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

    abstract protected function createHtmlValue(AbstractModelSingle $model): Html;
}

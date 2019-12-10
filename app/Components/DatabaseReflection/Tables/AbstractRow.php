<?php

namespace FKSDB\Components\DatabaseReflection;

use FKSDB\Components\Controls\Helpers\Badges\PermissionDeniedBadge;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;
use Nette\Localization\ITranslator;
use Nette\NotImplementedException;
use Nette\SmartObject;
use Nette\Utils\Html;

/**
 * Class AbstractField
 * @package FKSDB\Components\Forms\Factories
 */
abstract class AbstractRow {
    use SmartObject;

    const PERMISSION_USE_GLOBAL_ACL = 1;
    const PERMISSION_ALLOW_BASIC = 16;
    const PERMISSION_ALLOW_RESTRICT = 128;
    CONST PERMISSION_ALLOW_FULL = 1024;
    /**
     * @var ITranslator
     */
    protected $translator;

    /**
     * AbstractField constructor.
     * @param ITranslator $translator
     */
    public function __construct(ITranslator $translator) {
        $this->translator = $translator;
    }

    /**
     * @return BaseControl
     */
    public function createField(): BaseControl {
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
     */
    public final function renderValue(AbstractModelSingle $model, int $userPermissionsLevel): Html {
        if (!$this->hasPermissions($userPermissionsLevel)) {
            return PermissionDeniedBadge::getHtml();
        }
        return $this->createHtmlValue($model);
    }

    /**
     * @param AbstractModelSingle $model
     * @return Html
     */
    abstract protected function createHtmlValue(AbstractModelSingle $model): Html;

    /**
     * @param int $userValue
     * @return bool
     */
    protected final function hasPermissions(int $userValue): bool {
        return $userValue >= $this->getPermissionsValue();
    }

    /**
     * @return string
     */
    public function getTableName(): string {
        throw new NotImplementedException();
    }

    /**
     * @return string
     */
    public function getModelClassName(): string {
        throw new NotImplementedException();
    }

    /**
     * @return int
     */
    abstract public function getPermissionsValue(): int;

    /**
     * @return string
     */
    abstract public function getTitle(): string;


}

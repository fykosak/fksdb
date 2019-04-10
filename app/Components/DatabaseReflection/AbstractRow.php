<?php

namespace FKSDB\Components\DatabaseReflection;

use FKSDB\Components\Controls\Helpers\ValuePrinters\StringValueControl;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\IControl;
use Nette\Localization\ITranslator;
use Nette\Utils\Html;

/**
 * Class AbstractField
 * @package FKSDB\Components\Forms\Factories
 */
abstract class AbstractRow {
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
     * @return TextInput
     */
    public function createField(): IControl {
        return new TextInput($this->getTitle());
    }

    /**
     * @param AbstractModelSingle $model
     * @param string $fieldName
     * @param int $userPermissionsLevel
     * @return \Nette\Utils\Html
     */
    public function createHtmlValue(AbstractModelSingle $model, string $fieldName, int $userPermissionsLevel): Html {
        return StringValueControl::renderStatic($model, $fieldName, $this->hasPermissions($userPermissionsLevel));
    }

    /**
     * @param int $userValue
     * @return bool
     */
    protected final function hasPermissions(int $userValue): bool {
        return $userValue >= $this->getPermissionsValue();
    }

    /**
     * @return int
     */
    abstract public function getPermissionsValue(): int;

    /**
     * @return string
     */
    abstract public static function getTitle(): string;
}

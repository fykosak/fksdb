<?php

namespace FKSDB\Components\DatabaseReflection;

use FKSDB\Components\Controls\Helpers\ValuePrinters\AbstractValue;
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
     * @return string
     */
    abstract public static function getTitle(): string;

    /**
     * @return TextInput
     */
    public function createField(): IControl {
        return new TextInput($this->getTitle());
    }

    /**
     * @param string $mode
     * @param int $userPermissionsLevel
     * @return AbstractValue
     */
    protected function createValuePrinter(string $mode, int $userPermissionsLevel): AbstractValue {
        return new StringValueControl($this->translator, $mode, $this->getTitle(), $this->hasPermissions($userPermissionsLevel));
    }

    /**
     * @param int $userPermissionsLevel
     * @return AbstractValue
     */
    public final function createStalkingRow(int $userPermissionsLevel): AbstractValue {
        return $this->createValuePrinter(AbstractValue::LAYOUT_STALKING, $userPermissionsLevel);
    }

    /**
     * @param AbstractModelSingle $model
     * @param string $fieldName
     * @param int $userPermissionsLevel
     * @return \Nette\Utils\Html
     */
    public final function createHtmlValue(AbstractModelSingle $model, string $fieldName, int $userPermissionsLevel): Html {
        return $this->createValuePrinter(AbstractValue::LAYOUT_NONE, $userPermissionsLevel)->createGridItem($model, $fieldName);
    }

    /**
     * @return int
     */
    abstract public function getPermissionsValue(): int;

    /**
     * @param int $userValue
     * @return bool
     */
    public function hasPermissions(int $userValue): bool {
        return $userValue >= $this->getPermissionsValue();
    }
}

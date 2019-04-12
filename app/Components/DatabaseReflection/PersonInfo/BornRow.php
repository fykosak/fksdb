<?php

namespace FKSDB\Components\DatabaseReflection\PersonInfo;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\Components\DatabaseReflection\ValuePrinters\DatePrinter;
use FKSDB\Components\Forms\Controls\WriteOnlyDatePicker;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;

/**
 * Class BornField
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
class BornRow extends AbstractRow {
    /**
     * @return string
     */
    public static function getTitle(): string {
        return _('Datum narození');
    }

    /**
     * @return BaseControl
     */
    public function createField(): BaseControl {
        $control = new WriteOnlyDatePicker($this->getTitle());
        $control->setDefaultDate((new \DateTime())->modify('-16 years'));
        return $control;
    }

    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_ALLOW_FULL;
    }

    /**
     * @param AbstractModelSingle $model
     * @param string $fieldName
     * @return Html
     */
    public function createHtmlValue(AbstractModelSingle $model, string $fieldName): Html {
        return (new DatePrinter)($model->{$fieldName}, 'd.m.Y');
    }
}

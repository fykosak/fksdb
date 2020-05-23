<?php

namespace FKSDB\Components\DatabaseReflection\PersonInfo;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\Components\DatabaseReflection\ValuePrinters\DatePrinter;
use FKSDB\Components\Forms\Controls\WriteOnlyDatePicker;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelPersonInfo;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;

/**
 * Class BornField
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
class BornRow extends AbstractRow {

    public function getTitle(): string {
        return _('Datum narození');
    }

    /**
     * @param array $args
     * @return BaseControl
     * @throws \Exception
     */
    public function createField(...$args): BaseControl {
        $control = new WriteOnlyDatePicker($this->getTitle());
        $control->setDefaultValue((new \DateTime())->modify('-16 years'));
        return $control;
    }

    public function getPermissionsValue(): int {
        return self::PERMISSION_ALLOW_FULL;
    }

    /**
     * @param AbstractModelSingle|ModelPersonInfo $model
     * @return Html
     */
    public function createHtmlValue(AbstractModelSingle $model): Html {
        return (new DatePrinter('d.m.Y'))($model->born);
    }
}

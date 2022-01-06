<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\PersonInfo;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ValuePrinters\DatePrinter;
use FKSDB\Components\Forms\Controls\WriteOnly\WriteOnlyDatePicker;
use Fykosak\NetteORM\AbstractModel;
use FKSDB\Models\ORM\Models\ModelPersonInfo;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;

class BornColumnFactory extends ColumnFactory {

    protected function createFormControl(...$args): BaseControl {
        $control = new WriteOnlyDatePicker($this->getTitle());
        $control->setDefaultValue((new \DateTime())->modify('-16 years'));
        return $control;
    }

    /**
     * @param AbstractModel|ModelPersonInfo $model
     */
    protected function createHtmlValue(AbstractModel $model): Html {
        return (new DatePrinter('d.m.Y'))($model->born);
    }
}

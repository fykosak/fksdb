<?php

namespace FKSDB\Components\DatabaseReflection\Event;

use FKSDB\Components\DatabaseReflection\ValuePrinters\DatePrinter;
use FKSDB\Components\Forms\Controls\DateInputs\DateTimeLocalInput;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelEvent;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;
use Nette\Utils\Html;

/**
 * Class BeginRow
 * *
 */
class BeginRow extends AbstractEventRowFactory {
    public function getTitle(): string {
        return _('Event begin');
    }

    /**
     * @param array $args
     * @return BaseControl
     */
    public function createField(...$args): BaseControl {
        $control = new DateTimeLocalInput($this->getTitle());
        $control->addRule(Form::FILLED, _('%label je povinný.'));
        return $control;
    }

    /**
     * @param AbstractModelSingle|ModelEvent $model
     * @return Html
     */
    public function createHtmlValue(AbstractModelSingle $model): Html {
        return (new DatePrinter('d.m.Y'))($model->begin);
    }
}

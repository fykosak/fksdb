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
 * Class EndRow
 * @package FKSDB\Components\DatabaseReflection\Event
 */
class EndRow extends AbstractEventRowFactory {

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Event end');
    }

    /**
     * @return null|string
     */
    public function getDescription() {
        return _('U jednodenních akcí shodný se začátkem.');
    }

    /**
     * @return BaseControl
     */
    public function createField(): BaseControl {
        $control = new DateTimeLocalInput($this->getTitle());
        $control->addRule(Form::FILLED, _('%label je povinný.'))
            ->setOption('description', $this->getDescription());
        return $control;
    }

    /**
     * @param AbstractModelSingle|ModelEvent $model
     * @param string $fieldName
     * @return Html
     */
    public function createHtmlValue(AbstractModelSingle $model, string $fieldName): Html {
        return (new DatePrinter('d.m.Y'))($model->end);
    }
}

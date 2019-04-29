<?php

namespace FKSDB\Components\DatabaseReflection\Event;

use FKSDB\Components\DatabaseReflection\ValuePrinters\DatePrinter;
use FKSDB\Components\Forms\Controls\DateInputs\DateTimeLocalInput;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelEvent;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;

/**
 * Class RegistrationBeginRow
 * @package FKSDB\Components\DatabaseReflection\Event
 */
class RegistrationBeginRow extends AbstractEventRowFactory {

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Registration begin');
    }

    /**
     * @return BaseControl
     */
    public function createField(): BaseControl {
        return new DateTimeLocalInput($this->getTitle());
    }

    /**
     * @param AbstractModelSingle|ModelEvent $model
     * @param string $fieldName
     * @return Html
     */
    public function createHtmlValue(AbstractModelSingle $model, string $fieldName): Html {
        return (new DatePrinter('d.m.Y H:i:s'))($model->registration_begin);
    }

}

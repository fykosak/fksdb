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
 * *
 */
class RegistrationBeginRow extends AbstractEventRowFactory {

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Registration begin');
    }

    /**
     * @param array $args
     * @return BaseControl
     */
    public function createField(...$args): BaseControl {
        return new DateTimeLocalInput($this->getTitle());
    }

    /**
     * @param AbstractModelSingle|ModelEvent $model
     * @return Html
     */
    public function createHtmlValue(AbstractModelSingle $model): Html {
        return (new DatePrinter('d.m.Y H:i:s'))($model->registration_begin);
    }

}

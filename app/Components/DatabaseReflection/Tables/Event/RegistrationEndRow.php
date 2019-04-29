<?php

namespace FKSDB\Components\DatabaseReflection\Event;

use FKSDB\Components\DatabaseReflection\ValuePrinters\DatePrinter;
use FKSDB\Components\Forms\Controls\DateInputs\DateTimeLocalInput;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelEvent;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;

/**
 * Class RegistrationEndRow
 * @package FKSDB\Components\DatabaseReflection\Event
 */
class RegistrationEndRow extends AbstractEventRowFactory {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Registration end');
    }

    /**
     * @return BaseControl
     */
    public function createField(): BaseControl {
        return new DateTimeLocalInput(self::getTitle());
    }

    /**
     * @param AbstractModelSingle|ModelEvent $model
     * @param string $fieldName
     * @return Html
     */
    public function createHtmlValue(AbstractModelSingle $model, string $fieldName): Html {
        return (new DatePrinter('d.m.Y H:i:s'))($model->registration_end);
    }
}

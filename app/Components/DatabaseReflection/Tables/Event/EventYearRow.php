<?php

namespace FKSDB\Components\DatabaseReflection\Event;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;

/**
 * Class EventYearRow
 * *
 */
class EventYearRow extends AbstractEventRowFactory {
    use DefaultPrinterTrait;

    public function getTitle(): string {
        return _('Event year');
    }

    /**
     * @param array $args
     * @return BaseControl
     */
    public function createField(...$args): BaseControl {
        $control = parent::createField($args);
        $control->addRule(Form::INTEGER, _('%label musí být číslo.'))
            ->addRule(Form::FILLED, _('%label je povinný.'))
            ->setOption('description', _('Ročník akce musí být unikátní pro daný typ akce.'));
        return $control;
    }

    protected function getModelAccessKey(): string {
        return 'event_year';
    }
}

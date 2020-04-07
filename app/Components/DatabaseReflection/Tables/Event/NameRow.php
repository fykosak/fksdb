<?php

namespace FKSDB\Components\DatabaseReflection\Event;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;

/**
 * Class NameRow
 * @package FKSDB\Components\DatabaseReflection\Event
 */
class NameRow extends AbstractEventRowFactory {
    use DefaultPrinterTrait;

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Name');
    }

    /**
     * @return null|string
     */
    public function getDescription() {
        return _('U soustředka místo.');
    }

    /**
     * @param array $args
     * @return BaseControl
     */
    public function createField(...$args): BaseControl {
        $control = parent::createField($args);
        $control->addRule(Form::FILLED, _('%label je povinný.'))
            ->addRule(Form::MAX_LENGTH, null, 255)
            ->setOption('description', $this->getDescription());
        return $control;
    }

    /**
     * @return string
     */
    protected function getModelAccessKey(): string {
        return 'name';
    }
}

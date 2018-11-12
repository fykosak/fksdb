<?php

namespace FKSDB\Components\Forms\Controls\EventPayment;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\EventPayment\PriceCalculator\PriceCalculator;
use FKSDB\ORM\ModelEventPayment;
use Nette\Application\UI\Control;
use Nette\Localization\ITranslator;
use Nette\Templating\FileTemplate;

/**
 * Class DetailControl
 * @package FKSDB\Components\Forms\Controls\EventPayment
 * @property FileTemplate $template
 */
class DetailControl extends Control {
    /**
     * @var ModelEventPayment
     */
    private $model;
    /**
     * @var ITranslator
     */
    private $translator;
    /**
     * @var PriceCalculator
     */
    private $calculator;

    public function __construct(ITranslator $translator, PriceCalculator $calculator, ModelEventPayment $model) {
        parent::__construct();
        $this->model = $model;
        $this->translator = $translator;
        $this->calculator = $calculator;
    }

    public function createComponentForm() {
        return new FormControl();
    }

    /**
     * @return FormControl
     */
    public function getFormControl() {
        return $this['form'];
    }

    public function render() {
        //$data = \json_decode($this->model->data);
        $data = [
            'accommodated_person_ids' => [94, 95],
            'event_participants' => [],
        ];
        $this->template->items = $this->calculator->getGridItems($data);
        $this->template->model = $this->model;
        $this->template->setTranslator($this->translator);
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'DetailControl.latte');
        $this->template->render();
    }

}

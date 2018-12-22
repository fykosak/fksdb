<?php

namespace FKSDB\Components\Forms\Controls\EventPayment;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Grids\Payment\PaymentStateLabel;
use FKSDB\EventPayment\PriceCalculator\Price;
use FKSDB\EventPayment\PriceCalculator\PriceCalculator;
use FKSDB\ORM\ModelPayment;
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
     * @var ModelPayment
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

    public function __construct(ITranslator $translator, PriceCalculator $calculator, ModelPayment $model) {
        parent::__construct();
        $this->model = $model;
        $this->translator = $translator;
        $this->calculator = $calculator;
    }

    public function createComponentForm() {
        return new FormControl();
    }

    public function createComponentStateDisplay() {
        return new StateDisplayControl($this->translator, $this->model);
    }

    public function createComponentStateLabel() {
        return new PaymentStateLabel($this->model, $this->translator);
    }

    public function createComponentPriceControl(Price $price) {
        return new PriceControl($this->translator, $price);
    }

    /**
     * @return FormControl
     */
    public function getFormControl() {
        return $this['form'];
    }

    public function render() {
        //$data = \json_decode($this->model->data);
        $this->calculator->setCurrency($this->model->currency);

        $this->template->items = $this->calculator->getGridItems($this->model);
        $this->template->model = $this->model;
        $this->template->setTranslator($this->translator);
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'DetailControl.latte');
        $this->template->render();
    }
}

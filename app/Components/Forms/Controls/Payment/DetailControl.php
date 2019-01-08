<?php

namespace FKSDB\Components\Forms\Controls\Payment;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Controls\Transitions\TransitionButtonsControl;
use FKSDB\Components\Grids\Payment\PaymentStateLabel;
use FKSDB\ORM\ModelPayment;
use FKSDB\Payment\PriceCalculator\Price;
use FKSDB\Payment\Transition\PaymentMachine;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Localization\ITranslator;
use Nette\Templating\FileTemplate;

/**
 * Class DetailControl
 * @package FKSDB\Components\Forms\Controls\Payment
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
     * @var PaymentMachine
     */
    private $machine;

    public function __construct(ITranslator $translator, PaymentMachine $machine, ModelPayment $model) {
        parent::__construct();
        $this->model = $model;
        $this->machine = $machine;
        $this->translator = $translator;
    }

    public function createComponentForm(): FormControl {
        $formControl = new FormControl();
        $form = $formControl->getForm();
        if ($this->model->canEdit()) {
            $form->addSubmit('edit', _('Edit payment'));
        }
        return $formControl;
    }

    public function createComponentStateDisplay(): StateDisplayControl {
        return new StateDisplayControl($this->translator, $this->model);
    }

    public function createComponentStateLabel(): PaymentStateLabel {
        return new PaymentStateLabel($this->model, $this->translator);
    }

    public function createComponentPriceControl(Price $price): PriceControl {
        return new PriceControl($this->translator, $price);
    }

    public function createComponentTransitionButtons() {
        return new TransitionButtonsControl($this->machine, $this->translator, $this->model);
    }

    /**
     * @return FormControl
     */
    public function getFormControl(): FormControl {
        /**
         * @var $control FormControl
         */
        $control = $this->getComponent('form');
        return $control;
    }

    public function render() {
        $this->machine->getPriceCalculator()->setCurrency($this->model->currency);

        $this->template->items = $this->machine->getPriceCalculator()->getGridItems($this->model);
        $this->template->model = $this->model;
        $this->template->setTranslator($this->translator);
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'DetailControl.latte');
        $this->template->render();
    }
}

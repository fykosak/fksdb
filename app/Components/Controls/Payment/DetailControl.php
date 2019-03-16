<?php

namespace FKSDB\Components\Controls\Payment;

use EventModule\PaymentPresenter;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Controls\Transitions\TransitionButtonsControl;
use FKSDB\ORM\Models\ModelPayment;
use FKSDB\Payment\PriceCalculator\Price;
use FKSDB\Payment\Transition\PaymentMachine;
use Nette\Application\UI\Control;
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

    /**
     * DetailControl constructor.
     * @param ITranslator $translator
     * @param PaymentMachine $machine
     * @param \FKSDB\ORM\Models\ModelPayment $model
     */
    public function __construct(ITranslator $translator, PaymentMachine $machine, ModelPayment $model) {
        parent::__construct();
        $this->model = $model;
        $this->machine = $machine;
        $this->translator = $translator;
    }

    /**
     * @return FormControl
     */
    public function createComponentForm(): FormControl {
        $formControl = new FormControl();
        $form = $formControl->getForm();
        /**
         * @var PaymentPresenter $presenter
         */
        $presenter = $this->getPresenter();
        if ($this->model->canEdit() || $presenter->getContestAuthorizator()->isAllowed($this->model, 'org', $this->model->getEvent()->getContest())) {
            $submit = $form->addSubmit('edit', _('Edit items'));
            $submit->onClick[] = function () {
                $this->getPresenter()->redirect('edit');
            };
        }
        return $formControl;
    }

    /**
     * @return StateDisplayControl
     */
    public function createComponentStateDisplay(): StateDisplayControl {
        return new StateDisplayControl($this->translator, $this->model);
    }

    /**
     * @return PaymentStateLabel
     */
    public function createComponentStateLabel(): PaymentStateLabel {
        return new PaymentStateLabel($this->model, $this->translator);
    }

    /**
     * @param Price $price
     * @return PriceControl
     */
    public function createComponentPriceControl(Price $price): PriceControl {
        return new PriceControl($this->translator, $price);
    }

    /**
     * @return TransitionButtonsControl
     */
    public function createComponentTransitionButtons() {
        return new TransitionButtonsControl($this->machine, $this->translator, $this->model);
    }

    /**
     * @return FormControl
     */
    public function getFormControl(): FormControl {
        /**
         * @var FormControl $control
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

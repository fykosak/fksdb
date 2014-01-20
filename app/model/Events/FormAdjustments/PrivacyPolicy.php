<?php

namespace Events\FormAdjustments;

use Events\Machine\BaseMachine;
use Events\Machine\Machine;
use Events\Model\Holder\Holder;
use Events\Processings\IProcessing;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FormUtils;
use Nette\Application\UI\Control;
use Nette\ArrayHash;
use Nette\Forms\Form;
use Nette\Object;

/**
 * Checks determining fields in sent data and either terminates the application
 * or tries to find unambiguous transition from the initial state.
 * 
 * @note Transition conditions are evaluated od pre-edited data.
 * @note All determining fields must be filled to consider application complete.
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class PrivacyPolicy extends Object implements IProcessing, IFormAdjustment {

    const CONTROL_NAME = 'privacy';

    /**
     * @var PersonFactory
     */
    private $personFactory;

    function __construct(PersonFactory $personFactory) {
        $this->personFactory = $personFactory;
    }

    public function adjust(Form $form, Machine $machine, Holder $holder) {
        if ($machine->getPrimaryMachine()->getState() != BaseMachine::STATE_INIT) {
            return;
        }


        $control = $this->personFactory->createAgreed();
        $control->addRule(Form::FILLED, _('Před odesláním je třeba potvrdit souhlas se zpracováním osobních údajů.'));

        $firstSubmit = FormUtils::findFirstSubmit($form);
        $form->addComponent($control, self::CONTROL_NAME, $firstSubmit->getName());
    }

    public function process(Control $control, ArrayHash $values, Machine $machine, Holder $holder) {
        //TODO think about what it should actually do (set agreed to all related persons or whoever?)
        //     and possibly restrict conditions when checkbox is added at all
    }

}

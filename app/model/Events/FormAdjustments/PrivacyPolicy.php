<?php

namespace Events\FormAdjustments;

use Events\Machine\BaseMachine;
use Events\Machine\Machine;
use Events\Model\Holder\Holder;
use Events\Processings\IProcessing;
use FKS\Logging\ILogger;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FKSDB\Components\Forms\Factories\PersonInfo\AgreedField;
use FormUtils;
use Nette\ArrayHash;
use Nette\Forms\Form;
use Nette\Object;
use ServicePersonInfo;

/**
 * Creates required checkbox for whole application and then
 * sets agreed bit in all person_info containers found (even for editations).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class PrivacyPolicy extends Object implements IProcessing, IFormAdjustment {

    const CONTROL_NAME = 'privacy';

    /**
     * @var PersonFactory
     */
    private $personFactory;

    /**
     * @var ServicePersonInfo
     */
    private $servicePersonInfo;

    function __construct(PersonFactory $personFactory, ServicePersonInfo $servicePersonInfo) {
        $this->personFactory = $personFactory;
        $this->servicePersonInfo = $servicePersonInfo;
    }

    public function adjust(Form $form, Machine $machine, Holder $holder) {
        if ($machine->getPrimaryMachine()->getState() != BaseMachine::STATE_INIT) {
            return;
        }


        $control = new AgreedField();
        $control->addRule(Form::FILLED, _('Před odesláním je třeba potvrdit souhlas se zpracováním osobních údajů.'));

        $firstSubmit = FormUtils::findFirstSubmit($form);
        $form->addComponent($control, self::CONTROL_NAME, $firstSubmit->getName());
    }

    public function process($states, ArrayHash $values, Machine $machine, Holder $holder, ILogger $logger, Form $form = null) {
        $this->trySetAgreed($values);
    }

    private function trySetAgreed(ArrayHash $values) {
        foreach ($values as $key => $value) {
            if ($value instanceof ArrayHash) {
                $this->trySetAgreed($value);
            } else {
                if (isset($values[$key . '_1']) && isset($values[$key . '_1']['person_info'])) {
                    $personId = $value;
                    $personInfo = $this->servicePersonInfo->findByPrimary($personId);
                    if ($personInfo) {
                        $this->servicePersonInfo->updateModel($personInfo, array('agreed' => 1));
			// This is done in ApplicationHandler transaction, still can be rolled back.
                        $this->servicePersonInfo->save($personInfo);
                        $values[$key . '_1']['person_info']['agreed'] = 1;
                    }
                }
            }
        }
    }

}

<?php

namespace FKSDB\Events\FormAdjustments;

use FKSDB\Events\Machine\BaseMachine;
use FKSDB\Events\Machine\Machine;
use FKSDB\Events\Model\Holder\Holder;
use FKSDB\Events\Processings\IProcessing;
use FKSDB\Components\Forms\Factories\PersonInfoFactory;
use FKSDB\Logging\ILogger;
use FKSDB\ORM\Services\ServicePersonInfo;
use FormUtils;
use Nette\Forms\Form;
use Nette\SmartObject;
use Nette\Utils\ArrayHash;

/**
 * Creates required checkbox for whole application and then
 * sets agreed bit in all person_info containers found (even for editations).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class PrivacyPolicy implements IProcessing, IFormAdjustment {

    use SmartObject;

    const CONTROL_NAME = 'privacy';

    /**
     * @var ServicePersonInfo
     */
    private $servicePersonInfo;
    /**
     * @var PersonInfoFactory
     */
    private $personInfoFactory;

    /**
     * PrivacyPolicy constructor.
     * @param ServicePersonInfo $servicePersonInfo
     * @param PersonInfoFactory $personInfoFactory
     */
    public function __construct(ServicePersonInfo $servicePersonInfo, PersonInfoFactory $personInfoFactory) {
        $this->servicePersonInfo = $servicePersonInfo;
        $this->personInfoFactory = $personInfoFactory;
    }

    /**
     * @param Form $form
     * @param Machine $machine
     * @param Holder $holder
     * @throws \Exception
     */
    public function adjust(Form $form, Machine $machine, Holder $holder) {
        if ($holder->getPrimaryHolder()->getModelState() != BaseMachine::STATE_INIT) {
            return;
        }

        $control = $this->personInfoFactory->createField('agreed');
        $control->addRule(Form::FILLED, _('Před odesláním je třeba potvrdit souhlas se zpracováním osobních údajů.'));

        $firstSubmit = FormUtils::findFirstSubmit($form);
        $form->addComponent($control, self::CONTROL_NAME, $firstSubmit->getName());
    }

    /**
     * @param $states
     * @param ArrayHash $values
     * @param Machine $machine
     * @param Holder $holder
     * @param ILogger $logger
     * @param Form|null $form
     * @throws \Exception
     */
    public function process($states, ArrayHash $values, Machine $machine, Holder $holder, ILogger $logger, Form $form = null) {
        $this->trySetAgreed($values);
    }

    /**
     * @param ArrayHash $values
     * @throws \Exception
     */
    private function trySetAgreed(ArrayHash $values) {
        foreach ($values as $key => $value) {
            if ($value instanceof ArrayHash) {
                $this->trySetAgreed($value);
            } elseif (isset($values[$key . '_1']) && isset($values[$key . '_1']['person_info'])) {
                $personId = $value;
                $personInfo = $this->servicePersonInfo->findByPrimary($personId);
                if ($personInfo) {

                    $this->servicePersonInfo->updateModel($personInfo, ['agreed' => 1]);

                    // This is done in ApplicationHandler transaction, still can be rolled back.
                    $this->servicePersonInfo->save($personInfo);
                    $values[$key . '_1']['person_info']['agreed'] = 1;
                }
            }
        }
    }
}

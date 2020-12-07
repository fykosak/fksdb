<?php

namespace FKSDB\Model\Events\FormAdjustments;

use FKSDB\Model\DBReflection\ColumnFactories\AbstractColumnException;
use FKSDB\Model\DBReflection\OmittedControlException;
use FKSDB\Components\Forms\Factories\SingleReflectionFormFactory;
use FKSDB\Model\Events\Machine\BaseMachine;
use FKSDB\Model\Events\Machine\Machine;
use FKSDB\Model\Events\Model\Holder\Holder;
use FKSDB\Model\Events\Processing\IProcessing;
use FKSDB\Model\Exceptions\BadTypeException;
use FKSDB\Model\Logging\ILogger;
use FKSDB\Model\ORM\Services\ServicePersonInfo;
use FKSDB\Model\Utils\FormUtils;
use Nette\Forms\Form;
use Nette\SmartObject;
use Nette\Utils\ArrayHash;

/**
 * Creates required checkbox for whole application and then
 * sets agreed bit in all person_info containers found (even for editing).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class PrivacyPolicy implements IProcessing, IFormAdjustment {

    use SmartObject;

    protected const CONTROL_NAME = 'privacy';

    private ServicePersonInfo $servicePersonInfo;

    private SingleReflectionFormFactory $singleReflectionFormFactory;

    public function __construct(ServicePersonInfo $servicePersonInfo, SingleReflectionFormFactory $singleReflectionFormFactory) {
        $this->servicePersonInfo = $servicePersonInfo;
        $this->singleReflectionFormFactory = $singleReflectionFormFactory;
    }

    /**
     * @param Form $form
     * @param Machine $machine
     * @param Holder $holder
     * @return void
     * @throws AbstractColumnException
     * @throws OmittedControlException
     * @throws BadTypeException
     */
    public function adjust(Form $form, Machine $machine, Holder $holder): void {
        if ($holder->getPrimaryHolder()->getModelState() != BaseMachine::STATE_INIT) {
            return;
        }

        $control = $this->singleReflectionFormFactory->createField('person_info', 'agreed');
        $control->addRule(Form::FILLED, _('You have to agree with the privacy policy before submitting.'));

        $firstSubmit = FormUtils::findFirstSubmit($form);
        $form->addComponent($control, self::CONTROL_NAME, $firstSubmit->getName());
    }

    /**
     * @param array $states
     * @param ArrayHash $values
     * @param Machine $machine
     * @param Holder $holder
     * @param ILogger $logger
     * @param Form|null $form
     * @return void
     */
    public function process(array $states, ArrayHash $values, Machine $machine, Holder $holder, ILogger $logger, ?Form $form = null) {
        $this->trySetAgreed($values);
    }

    private function trySetAgreed(ArrayHash $values): void {
        foreach ($values as $key => $value) {
            if ($value instanceof ArrayHash) {
                $this->trySetAgreed($value);
            } elseif (isset($values[$key . '_1']) && isset($values[$key . '_1']['person_info'])) {
                $personId = $value;
                $personInfo = $this->servicePersonInfo->findByPrimary($personId);
                if ($personInfo) {

                    $this->servicePersonInfo->updateModel2($personInfo, ['agreed' => 1]);

                    // This is done in ApplicationHandler transaction, still can be rolled back.
                    //$this->servicePersonInfo->save($personInfo);
                    $values[$key . '_1']['person_info']['agreed'] = 1;
                }
            }
        }
    }
}

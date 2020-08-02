<?php

namespace FKSDB\Events\FormAdjustments;

use FKSDB\DBReflection\ColumnFactories\AbstractColumnException;
use FKSDB\DBReflection\OmittedControlException;
use FKSDB\Components\Forms\Factories\SingleReflectionFormFactory;
use FKSDB\Events\Machine\BaseMachine;
use FKSDB\Events\Machine\Machine;
use FKSDB\Events\Model\Holder\Holder;
use FKSDB\Events\Processings\IProcessing;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Logging\ILogger;
use FKSDB\ORM\Services\ServicePersonInfo;
use FKSDB\Utils\FormUtils;
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

    const CONTROL_NAME = 'privacy';

    /** @var ServicePersonInfo */
    private $servicePersonInfo;
    /** @var SingleReflectionFormFactory */
    private $singleReflectionFormFactory;

    /**
     * PrivacyPolicy constructor.
     * @param ServicePersonInfo $servicePersonInfo
     * @param SingleReflectionFormFactory $singleReflectionFormFactory
     */
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
    public function adjust(Form $form, Machine $machine, Holder $holder) {
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
    public function process($states, ArrayHash $values, Machine $machine, Holder $holder, ILogger $logger, Form $form = null) {
        $this->trySetAgreed($values);
    }

    /**
     * @param ArrayHash $values
     * @return void
     */
    private function trySetAgreed(ArrayHash $values) {
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

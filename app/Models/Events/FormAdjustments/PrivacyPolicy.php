<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\FormAdjustments;

use FKSDB\Models\Events\Processing\Processing;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Columns\OmittedControlException;
use FKSDB\Models\ORM\ReflectionFactory;
use FKSDB\Models\ORM\Services\PersonInfoService;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Holder\ParticipantHolder;
use FKSDB\Models\Transitions\Machine\Machine;
use FKSDB\Models\Utils\FormUtils;
use Nette\Forms\Form;
use Nette\SmartObject;

/**
 * Creates required checkbox for whole application and then
 * sets agreed bit in all person_info containers found (even for editing).
 * @phpstan-implements FormAdjustment<ParticipantHolder>
 */
class PrivacyPolicy implements Processing, FormAdjustment
{
    use SmartObject;

    protected const CONTROL_NAME = 'privacy';
    private PersonInfoService $personInfoService;
    private ReflectionFactory $reflectionFactory;

    public function __construct(
        PersonInfoService $personInfoService,
        ReflectionFactory $reflectionFactory
    ) {
        $this->personInfoService = $personInfoService;
        $this->reflectionFactory = $reflectionFactory;
    }

    /**
     * @param ParticipantHolder $holder
     * @throws OmittedControlException
     * @throws BadTypeException
     */
    public function adjust(Form $form, ModelHolder $holder): void
    {
        if ($holder->getState() != Machine::STATE_INIT) {
            return;
        }

        $control = $this->reflectionFactory->createField('person_info', 'agreed');
        $control->addRule(Form::FILLED, _('You have to agree with the privacy policy before submitting.'));

        $firstSubmit = FormUtils::findFirstSubmit($form);
        $form->addComponent($control, self::CONTROL_NAME, $firstSubmit->getName());
    }

    public function process(array $values): array
    {
        return $this->trySetAgreed($values);
    }

    private function trySetAgreed(array $values): array
    {
        $newValues = [];
        foreach ($values as $key => $value) {
            if (is_array($value)) {
                $newValues[$key] = $this->trySetAgreed($value);
            } elseif (isset($values[$key . '_container']) && isset($values[$key . '_container']['person_info'])) {
                $newValues = $values;
                $personId = $value;
                $personInfo = $this->personInfoService->findByPrimary($personId);
                if ($personInfo) {
                    $this->personInfoService->storeModel(['agreed' => 1], $personInfo);

                    $newValues[$key . '_container']['person_info']['agreed'] = 1;
                }
            }
        }
        return $newValues;
    }
}

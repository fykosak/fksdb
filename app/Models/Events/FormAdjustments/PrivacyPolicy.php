<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\FormAdjustments;

use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Events\Processing\Processing;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Columns\OmittedControlException;
use FKSDB\Models\ORM\ReflectionFactory;
use FKSDB\Models\ORM\Services\PersonInfoService;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Machine\Machine;
use FKSDB\Models\Utils\FormUtils;
use Nette\Forms\Form;
use Nette\SmartObject;
use Nette\Utils\ArrayHash;

/**
 * Creates required checkbox for whole application and then
 * sets agreed bit in all person_info containers found (even for editing).
 * @phpstan-implements FormAdjustment<BaseHolder>
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
     * @param BaseHolder $holder
     * @throws OmittedControlException
     * @throws BadTypeException
     */
    public function adjust(Form $form, ModelHolder $holder): void
    {
        if ($holder->getModelState() != Machine::STATE_INIT) {
            return;
        }

        $control = $this->reflectionFactory->createField('person_info', 'agreed');
        $control->addRule(Form::FILLED, _('You have to agree with the privacy policy before submitting.'));

        $firstSubmit = FormUtils::findFirstSubmit($form);
        $form->addComponent($control, self::CONTROL_NAME, $firstSubmit->getName());
    }

    /**
     * @phpstan-param ArrayHash<ArrayHash<mixed>|mixed> $values
     */
    public function process(ArrayHash $values): void
    {
        $this->trySetAgreed($values);
    }

    /**
     * @phpstan-param ArrayHash<ArrayHash<mixed>|mixed> $values
     */
    private function trySetAgreed(ArrayHash $values): void
    {
        foreach ($values as $key => $value) {
            if ($value instanceof ArrayHash) {
                $this->trySetAgreed($value);
            } elseif (isset($values[$key . '_container']) && isset($values[$key . '_container']['person_info'])) {
                $personId = $value;
                $personInfo = $this->personInfoService->findByPrimary($personId);
                if ($personInfo) {
                    $this->personInfoService->storeModel(['agreed' => 1], $personInfo);

                    $values[$key . '_container']['person_info']['agreed'] = 1;
                }
            }
        }
    }
}

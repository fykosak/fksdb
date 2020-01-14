<?php

namespace FKSDB\Components\DatabaseReflection;

use FKSDB\Components\Controls\Helpers\Badges\NotSetBadge;
use FKSDB\Components\Controls\PhoneNumber\PhoneNumberFactory;
use FKSDB\Components\Forms\Controls\WriteOnlyInput;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Services\ServiceRegion;
use FKSDB\ValidationTest\ValidationLog;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;
use Nette\Utils\Html;

/**
 * Class IPhoneField
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
trait PhoneRowTrait {

    /**
     * @var PhoneNumberFactory
     */
    protected $phoneNumberFactory;

    /**
     * @return BaseControl
     */
    public function createField(): BaseControl {
        $control = new WriteOnlyInput($this->getTitle());
        $control->setAttribute('placeholder', _('+XXXXXXXXXXXX'));
        $control->addRule(Form::MAX_LENGTH, null, 32);
        $control->setOption('description', _('Use an international format, starting with "+"'));
        $control->addCondition(Form::FILLED)
            ->addRule(function (BaseControl $control) {
                if ($control->getValue() === WriteOnlyInput::VALUE_ORIGINAL) {
                    return true;
                }
                return $this->phoneNumberFactory->getFormValidationCallback()($control);
            }, _('Phone number is not valid. Please insert a valid number.'));
        return $control;
    }

    /**
     * @param AbstractModelSingle $model
     * @return ValidationLog
     */
    public final function runTest(AbstractModelSingle $model): ValidationLog {

        $value = $model->{$this->getModelAccessKey()};
        if (\is_null($value)) {
            return new ValidationLog($this->getTitle(), \sprintf('%s is not set', $this->getTitle()), ValidationLog::LVL_INFO);
        }
        if (!$this->phoneNumberFactory->isValid($value)) {
            return new ValidationLog($this->getTitle(), \sprintf('%s number (%s) is not valid', $this->getTitle(), $value), ValidationLog::LVL_DANGER);
        } else {
            return new ValidationLog($this->getTitle(), \sprintf('%s is valid', $this->getTitle()), ValidationLog::LVL_SUCCESS);
        }
    }

    /**
     * @return string
     * only must exists
     */
    abstract function getTitle(): string;

    /**
     * @param AbstractModelSingle $model
     * @return Html
     */
    public function createHtmlValue(AbstractModelSingle $model): Html {
        $value = $model->{$this->getModelAccessKey()};
        if (\is_null($value)) {
            return NotSetBadge::getHtml();
        } else {
            return $this->phoneNumberFactory->formatPhone($value);
        }
    }

    /**
     * @return string
     * only must exists
     */
    abstract function getModelAccessKey(): string;
}

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
     * @var ServiceRegion
     */
    private $serviceRegion;
    /**
     * @var PhoneNumberFactory
     */
    protected $phoneNumberFactory;

    /**
     * @return BaseControl
     */
    public function createField(): BaseControl {
        $control = new WriteOnlyInput($this->getTitle());
        $control->setAttribute('placeholder', _('ve tvaru +420123456789'));
        $control->addRule(Form::MAX_LENGTH, null, 32);
        $control->addCondition(Form::FILLED)
            ->addRule($this->phoneNumberFactory->getFormValidationCallback(), _('Phone number is not valid. Please use internation format, starting with "+"'));
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

<?php

namespace FKSDB\Components\DatabaseReflection;

use FKSDB\Components\Controls\PhoneNumber\PhoneNumberFactory;
use FKSDB\Components\DatabaseReflection\ValuePrinters\PhonePrinter;
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
     * @return BaseControl
     */
    public function createField(): BaseControl {
        $control = new WriteOnlyInput($this->getTitle());
        $control->setAttribute('placeholder', _('ve tvaru +420123456789'));
        $control->addRule(Form::MAX_LENGTH, null, 32);
        $control->addCondition(Form::FILLED)
            ->addRule(PhoneNumberFactory::getFormValidationCallback(), _('Phone number is not valid. Please use internation format, starting with "+"'));
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
        if (!PhoneNumberFactory::isValid($value)) {
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
     * @return ServiceRegion
     */
    protected function getServiceRegion(): ServiceRegion {
        return $this->serviceRegion;
    }

    /**
     * @param AbstractModelSingle $model
     * @param string $fieldName
     * @return Html
     */
    public function createHtmlValue(AbstractModelSingle $model, string $fieldName): Html {
        return (new PhonePrinter)($model->{$this->getModelAccessKey()});
    }

    /**
     * @return string
     * only must exists
     */
    abstract function getModelAccessKey(): string;
}

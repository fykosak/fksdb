<?php

namespace FKSDB\Components\DatabaseReflection;

use FKSDB\Components\Controls\Badges\NotSetBadge;
use FKSDB\Components\Controls\PhoneNumber\PhoneNumberFactory;
use FKSDB\Components\Forms\Controls\WriteOnlyInput;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\DataTesting\TestsLogger;
use FKSDB\DataTesting\TestLog;
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
     * @param array $args
     * @return BaseControl
     */
    public function createField(...$args): BaseControl {
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
     * @param TestsLogger $logger
     * @param AbstractModelSingle $model
     */
    public final function runTest(TestsLogger $logger, AbstractModelSingle $model) {

        $value = $model->{$this->getModelAccessKey()};
        if (\is_null($value)) {
            return;
        } elseif (!$this->phoneNumberFactory->isValid($value)) {
            $logger->log(new TestLog($this->getTitle(), \sprintf('%s number (%s) is not valid', $this->getTitle(), $value), TestLog::LVL_DANGER));
        } else {
            $logger->log(new TestLog($this->getTitle(), \sprintf('%s is valid', $this->getTitle()), TestLog::LVL_SUCCESS));
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

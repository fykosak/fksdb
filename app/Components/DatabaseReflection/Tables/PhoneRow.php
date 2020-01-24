<?php

namespace FKSDB\Components\DatabaseReflection\Tables;

use FKSDB\Components\Controls\Helpers\Badges\NotSetBadge;
use FKSDB\Components\Controls\PhoneNumber\PhoneNumberFactory;
use FKSDB\Components\DatabaseReflection\DefaultRow;
use FKSDB\Components\Forms\Controls\WriteOnlyInput;
use FKSDB\Components\Forms\Factories\ITestedRowFactory;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ValidationTest\ValidationLog;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\Localization\ITranslator;
use Nette\Utils\Html;

/**
 * Class PhoneRow
 * @package FKSDB\Components\DatabaseReflection\Tables
 */
class PhoneRow extends DefaultRow implements ITestedRowFactory {
    /**
     * @var PhoneNumberFactory
     */
    protected $phoneNumberFactory;
    /**
     * @var bool
     */
    private $isWriteOnly = true;

    /**
     * PhoneRow constructor.
     * @param PhoneNumberFactory $phoneNumberFactory
     * @param ITranslator $translator
     * @param MetaDataFactory $metaDataFactory
     */
    public function __construct(PhoneNumberFactory $phoneNumberFactory, ITranslator $translator, MetaDataFactory $metaDataFactory) {
        $this->phoneNumberFactory = $phoneNumberFactory;
        parent::__construct($translator, $metaDataFactory);
    }

    /**
     * @param bool $isWriteOnly
     */
    public function setWriteOnly(bool $isWriteOnly) {
        $this->isWriteOnly = $isWriteOnly;
    }

    /**
     * @return BaseControl
     */
    public function createField(): BaseControl {
        $control = null;
        if ($this->isWriteOnly) {
            $control = new WriteOnlyInput($this->getTitle());
        } else {
            $control = new TextInput($this->getTitle());
        }
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
}

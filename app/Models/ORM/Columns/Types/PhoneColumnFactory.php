<?php

namespace FKSDB\Models\ORM\Columns\Types;

use FKSDB\Components\Badges\NotSetBadge;
use FKSDB\Components\Forms\Controls\WriteOnly\WriteOnly;
use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\MetaDataFactory;
use FKSDB\Models\PhoneNumber\PhoneNumberFactory;
use FKSDB\Models\ORM\Columns\TestedColumnFactory;
use FKSDB\Components\Forms\Controls\WriteOnly\WriteOnlyInput;
use Fykosak\Utils\Logging\Logger;
use Fykosak\NetteORM\AbstractModel;
use FKSDB\Models\DataTesting\TestLog;
use Fykosak\Utils\Logging\Message;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\Utils\Html;

class PhoneColumnFactory extends ColumnFactory implements TestedColumnFactory {

    protected PhoneNumberFactory $phoneNumberFactory;

    private bool $isWriteOnly = true;

    public function __construct(PhoneNumberFactory $phoneNumberFactory, MetaDataFactory $metaDataFactory) {
        $this->phoneNumberFactory = $phoneNumberFactory;
        parent::__construct($metaDataFactory);
    }

    public function setWriteOnly(bool $isWriteOnly): void {
        $this->isWriteOnly = $isWriteOnly;
    }

    protected function createFormControl(...$args): BaseControl {
        $control = null;
        if ($this->isWriteOnly) {
            $control = new WriteOnlyInput($this->getTitle());
        } else {
            $control = new TextInput($this->getTitle());
        }
        $control->setHtmlAttribute('placeholder', _('+XXXXXXXXXXXX'));
        $control->addRule(Form::MAX_LENGTH, null, 32);
        $control->setOption('description', _('Use an international format, starting with "+"'));
        $control->addCondition(Form::FILLED)
            ->addRule(function (BaseControl $control): bool {
                if ($control->getValue() === WriteOnly::VALUE_ORIGINAL) {
                    return true;
                }
                return $this->phoneNumberFactory->isValid($control->getValue());
            }, _('Phone number is not valid. Please insert a valid number.'));
        return $control;
    }

    final public function runTest(Logger $logger, AbstractModel $model): void {

        $value = $model->{$this->getModelAccessKey()};
        if (\is_null($value)) {
            return;
        }
        if (!$this->phoneNumberFactory->isValid($value)) {
            $logger->log(new TestLog($this->getTitle(), \sprintf('%s number (%s) is not valid', $this->getTitle(), $value), Message::LVL_ERROR));
        } else {
            $logger->log(new TestLog($this->getTitle(), \sprintf('%s is valid', $this->getTitle()), Message::LVL_SUCCESS));
        }
    }

    protected function createHtmlValue(AbstractModel $model): Html {
        $value = $model->{$this->getModelAccessKey()};
        if (\is_null($value)) {
            return NotSetBadge::getHtml();
        } else {
            return $this->phoneNumberFactory->formatPhone($value);
        }
    }
}

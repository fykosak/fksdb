<?php

namespace FKSDB\DBReflection\ColumnFactories;

use FKSDB\Components\Controls\Badges\NotSetBadge;
use FKSDB\Components\Controls\PhoneNumber\PhoneNumberFactory;
use FKSDB\DBReflection\MetaDataFactory;
use FKSDB\Components\Forms\Controls\WriteOnlyInput;
use FKSDB\Logging\ILogger;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\DataTesting\TestLog;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\Utils\Html;

/**
 * Class PhoneRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PhoneColumnFactory extends DefaultColumnFactory implements ITestedColumnFactory {

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
        $control->setAttribute('placeholder', _('+XXXXXXXXXXXX'));
        $control->addRule(Form::MAX_LENGTH, null, 32);
        $control->setOption('description', _('Use an international format, starting with "+"'));
        $control->addCondition(Form::FILLED)
            ->addRule(function (BaseControl $control): bool {
                if ($control->getValue() === WriteOnlyInput::VALUE_ORIGINAL) {
                    return true;
                }
                return $this->phoneNumberFactory->isValid($control->getValue());
            }, _('Phone number is not valid. Please insert a valid number.'));
        return $control;
    }

    final public function runTest(ILogger $logger, AbstractModelSingle $model): void {

        $value = $model->{$this->getModelAccessKey()};
        if (\is_null($value)) {
            return;
        }
        if (!$this->phoneNumberFactory->isValid($value)) {
            $logger->log(new TestLog($this->getTitle(), \sprintf('%s number (%s) is not valid', $this->getTitle(), $value), TestLog::LVL_DANGER));
        } else {
            $logger->log(new TestLog($this->getTitle(), \sprintf('%s is valid', $this->getTitle()), TestLog::LVL_SUCCESS));
        }
    }

    protected function createHtmlValue(AbstractModelSingle $model): Html {
        $value = $model->{$this->getModelAccessKey()};
        if (\is_null($value)) {
            return NotSetBadge::getHtml();
        } else {
            return $this->phoneNumberFactory->formatPhone($value);
        }
    }
}

<?php

namespace FKSDB\Components\DatabaseReflection\ColumnFactories;

use FKSDB\Components\Controls\Badges\NotSetBadge;
use FKSDB\Components\Controls\PhoneNumber\PhoneNumberFactory;
use FKSDB\Components\DatabaseReflection\MetaDataFactory;
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
     * @param MetaDataFactory $metaDataFactory
     */
    public function __construct(PhoneNumberFactory $phoneNumberFactory, MetaDataFactory $metaDataFactory) {
        $this->phoneNumberFactory = $phoneNumberFactory;
        parent::__construct($metaDataFactory);
    }

    /**
     * @param bool $isWriteOnly
     * @return void
     */
    public function setWriteOnly(bool $isWriteOnly) {
        $this->isWriteOnly = $isWriteOnly;
    }

    public function createFormControl(...$args): BaseControl {
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
                return $this->phoneNumberFactory->isValid($control->getValue());
            }, _('Phone number is not valid. Please insert a valid number.'));
        return $control;
    }

    /**
     * @param ILogger $logger
     * @param AbstractModelSingle $model
     * @return void
     */
    final public function runTest(ILogger $logger, AbstractModelSingle $model) {

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

    public function createHtmlValue(AbstractModelSingle $model): Html {
        $value = $model->{$this->getModelAccessKey()};
        if (\is_null($value)) {
            return NotSetBadge::getHtml();
        } else {
            return $this->phoneNumberFactory->formatPhone($value);
        }
    }
}

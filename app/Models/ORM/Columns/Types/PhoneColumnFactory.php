<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Types;

use FKSDB\Components\Badges\NotSetBadge;
use FKSDB\Components\Forms\Controls\WriteOnly\WriteOnly;
use FKSDB\Components\Forms\Controls\WriteOnly\WriteOnlyInput;
use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\Columns\TestedColumnFactory;
use FKSDB\Models\ORM\MetaDataFactory;
use FKSDB\Models\PhoneNumber\PhoneNumberFactory;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\Logging\Logger;
use Fykosak\Utils\Logging\Message;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\Utils\Html;

/**
 * @phpstan-extends ColumnFactory<Model,never>
 */
class PhoneColumnFactory extends ColumnFactory implements TestedColumnFactory
{
    protected PhoneNumberFactory $phoneNumberFactory;

    public function __construct(PhoneNumberFactory $phoneNumberFactory, MetaDataFactory $metaDataFactory)
    {
        $this->phoneNumberFactory = $phoneNumberFactory;
        parent::__construct($metaDataFactory);
    }

    protected function createFormControl(...$args): BaseControl
    {
        if ($this->isWriteOnly) {
            $control = new WriteOnlyInput($this->getTitle());
        } else {
            $control = new TextInput($this->getTitle());
        }
        $control->setHtmlAttribute('placeholder', _('+XXXXXXXXXXXX'));
        $control->addRule(Form::MAX_LENGTH, _('Max length reached'), 32);
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

    final public function runTest(Logger $logger, Model $model): void
    {

        $value = $model->{$this->modelAccessKey};
        if (\is_null($value)) {
            return;
        }
        if (!$this->phoneNumberFactory->isValid($value)) {
            $logger->log(
                new Message(
                    \sprintf(_('%s number (%s) is not valid'), $this->getTitle(), $value),
                    Message::LVL_ERROR
                )
            );
        } else {
            $logger->log(
                new Message(\sprintf(_('%s is valid'), $this->getTitle()), Message::LVL_SUCCESS)
            );
        }
    }

    protected function createHtmlValue(Model $model): Html
    {
        $value = $model->{$this->modelAccessKey};
        if (\is_null($value)) {
            return NotSetBadge::getHtml();
        } else {
            return $this->phoneNumberFactory->formatPhone($value);
        }
    }
}

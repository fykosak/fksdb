<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Types;

use FKSDB\Components\DataTest\TestLogger;
use FKSDB\Components\DataTest\TestMessage;
use FKSDB\Components\Forms\Controls\WriteOnly\WriteOnly;
use FKSDB\Components\Forms\Controls\WriteOnly\WriteOnlyInput;
use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\Columns\TestedColumnFactory;
use FKSDB\Models\PhoneNumber\PhoneNumberFactory;
use FKSDB\Models\UI\NotSetBadge;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Logging\Message;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\Utils\Html;

/**
 * @phpstan-template TModel of Model
 * @phpstan-template ArgType
 * @phpstan-extends ColumnFactory<TModel,ArgType>
 */
class PhoneColumnFactory extends ColumnFactory implements TestedColumnFactory
{
    protected PhoneNumberFactory $phoneNumberFactory;

    public function injectFactory(PhoneNumberFactory $phoneNumberFactory): void
    {
        $this->phoneNumberFactory = $phoneNumberFactory;
    }

    protected function createFormControl(...$args): BaseControl
    {
        if ($this->isWriteOnly) {
            $control = new WriteOnlyInput($this->getTitle());
        } else {
            $control = new TextInput($this->getTitle());
        }
        $control->setHtmlAttribute('placeholder', '+XXXXXXXXXXXX');
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

    /**
     * @phpstan-param TModel $model
     */
    final public function runTest(TestLogger $logger, Model $model, string $id): void
    {

        $value = $model->{$this->modelAccessKey};
        if (\is_null($value)) {
            return;
        }
        if (!$this->phoneNumberFactory->isValid($value)) {
            $logger->log(
                new TestMessage(
                    $id,
                    \sprintf(_('%s number (%s) is not valid'), $this->getTitle(), $value),
                    Message::LVL_ERROR
                )
            );
        }
    }

    /**
     * @phpstan-param TModel $model
     */
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

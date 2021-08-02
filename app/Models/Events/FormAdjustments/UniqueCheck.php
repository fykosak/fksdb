<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\FormAdjustments;

use FKSDB\Components\Forms\Controls\ReferencedId;
use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Events\Model\Holder\Holder;
use Nette\Forms\Control;
use Nette\Forms\Form;

class UniqueCheck extends AbstractAdjustment
{

    private string $field;

    private string $message;

    public function __construct(string $field, string $message)
    {
        $this->field = $field;
        $this->message = $message;
    }

    protected function innerAdjust(Form $form, Holder $holder): void
    {
        $controls = $this->getControl($this->field);
        if (!$controls) {
            return;
        }

        foreach ($controls as $name => $control) {
            $name = $holder->hasBaseHolder($name) ? $name : substr(
                $this->field,
                0,
                strpos($this->field, self::DELIMITER)
            );
            $baseHolder = $holder->getBaseHolder($name);
            $control->addRule(
                function (Control $control) use ($baseHolder): bool {
                    $table = $baseHolder->getService()->getTable();
                    $column = BaseHolder::getBareColumn($this->field);
                    if ($control instanceof ReferencedId) {
                        /* We don't want to fulfill potential promise
                         * as it would be out of transaction here.
                         */
                        $value = $control->getValue(false);
                    } else {
                        $value = $control->getValue();
                    }
                    $model = $baseHolder->getModel2();
                    $pk = $table->getName() . '.' . $table->getPrimary();

                    $table->where($column, $value);
                    $table->where(
                        $baseHolder->getEventIdColumn(),
                        $baseHolder->getHolder()->getPrimaryHolder()->getEvent()->getPrimary()
                    );
                    if ($model) {
                        $table->where("NOT $pk = ?", $model->getPrimary());
                    }
                    return count($table) == 0;
                },
                $this->message
            );
        }
    }
}

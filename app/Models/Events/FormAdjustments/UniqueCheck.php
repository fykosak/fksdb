<?php

namespace FKSDB\Models\Events\FormAdjustments;

use FKSDB\Components\Forms\Controls\ReferencedId;
use FKSDB\Models\Events\Machine\Machine;
use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Events\Model\Holder\Holder;
use Nette\Forms\Form;
use Nette\Forms\Control;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * @note Assumes the first part of the field name is the holder name or
 * the dynamic (wildCart) part represents the holder name.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class UniqueCheck extends AbstractAdjustment {

    private string $field;

    private string $message;

    public function __construct(string $field, string $message) {
        $this->field = $field;
        $this->message = $message;
    }

    protected function innerAdjust(Form $form, Machine $machine, Holder $holder): void {
        $controls = $this->getControl($this->field);
        if (!$controls) {
            return;
        }

        foreach ($controls as $name => $control) {
            $name = $holder->hasBaseHolder($name) ? $name : substr($this->field, 0, strpos($this->field, self::DELIMITER));
            $baseHolder = $holder->getBaseHolder($name);
            $control->addRule(function (Control $control) use ($baseHolder) : bool {
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
                $model = $baseHolder->getModel();
                $pk = $table->getName() . '.' . $table->getPrimary();

                $table->where($column, $value);
                $table->where($baseHolder->getEventIdColumn(), $baseHolder->getHolder()->getPrimaryHolder()->getEvent()->getPrimary());
                if ($model && !$model->isNew()) {
                    $table->where("NOT $pk = ?", $model->getPrimary());
                }
                return count($table) == 0;
            }, $this->message);
        }
    }
}

<?php

namespace Events\FormAdjustments;

use Events\Machine\Machine;
use Events\Model\Holder\BaseHolder;
use Events\Model\Holder\Holder;
use Nette\Forms\Form;
use Nette\Forms\IControl;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class UniqueCheck extends AbstractAdjustment {

    private $field;

    function __construct($field) {
        $this->field = $field;
    }

    protected function _adjust(Form $form, Machine $machine, Holder $holder) {
        $control = $this->getControl($this->field);
        $control = reset($control);
        if (!$control) {
            return;
        }

        $field = $this->field;
        $control->addRule(function(IControl $control) use($holder, $field) {
                    $table = $holder->getPrimaryHolder()->getService()->getTable();
                    $column = BaseHolder::getBareColumn($field);
                    $value = $control->getValue();
                    $model = $holder->getPrimaryHolder()->getModel();
                    $pk = $table->getPrimary();

                    $table->where($column, $value);
                    $table->where($holder->getPrimaryHolder()->getEventId(), $holder->getEvent()->getPrimary());
                    if ($model && !$model->isNew()) {
                        $table->where("NOT $pk = ?", $model->getPrimary());
                    }
                    return count($table) == 0;
                }, _("%label '%value' již existuje."));
    }

}

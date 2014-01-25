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
    private $message;

    function __construct($field, $message) {
        $this->field = $field;
        $this->message = $message;
    }

    protected function _adjust(Form $form, Machine $machine, Holder $holder) {
        $controls = $this->getControl($this->field);
        if (!$controls) {
            return;
        }

        foreach ($controls as $name => $control) {
            $field = $this->field;
            $name = $holder->hasBaseHolder($name) ? $name : substr($this->field, 0, strpos($this->field, self::DELIMITER));
            $baseHolder = $holder->getBaseHolder($name);
            $control->addRule(function(IControl $control) use($baseHolder, $field) {
                        $table = $baseHolder->getService()->getTable();
                        $column = BaseHolder::getBareColumn($field);
                        if ($control instanceof \FKS\Components\Forms\Controls\ReferencedId) {
                            $value = $control->getValue(false);
                        } else {
                            $value = $control->getValue();
                        }
                        $model = $baseHolder->getModel();
                        $pk = $table->getName() . '.' . $table->getPrimary();

                        $table->where($column, $value);
                        $table->where($baseHolder->getEventId(), $baseHolder->getHolder()->getEvent()->getPrimary());
                        if ($model && !$model->isNew()) {
                            $table->where("NOT $pk = ?", $model->getPrimary());
                        }
                        return count($table) == 0;
                    }, $this->message);
        }
    }

}

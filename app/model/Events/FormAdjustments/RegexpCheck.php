<?php

namespace FKSDB\Events\FormAdjustments;

use FKSDB\Events\Machine\Machine;
use FKSDB\Events\Model\Holder\Holder;
use Nette\Forms\Form;
use Nette\Forms\IControl;
use Nette\Utils\Strings;

/**
 * More user friendly Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class RegexpCheck extends AbstractAdjustment implements IFormAdjustment {

    private $field;
    private $message;
    private $pattern;

    /**
     * RegexpCheck constructor.
     * @param $field
     * @param $message
     * @param $pattern
     */
    public function __construct($field, $message, $pattern) {
        $this->field = $field;
        $this->message = $message;
        $this->pattern = $pattern;
    }

    /**
     * @param Form $form
     * @param Machine $machine
     * @param Holder $holder
     * @return void
     */
    protected function _adjust(Form $form, Machine $machine, Holder $holder) {
        $controls = $this->getControl($this->field);
        if (!$controls) {
            return;
        }

        foreach ($controls as $control) {
            $control->addRule(function (IControl $control) {
                return (bool)Strings::match($control->getValue(), $this->pattern);
            }, $this->message);
        }
    }
}

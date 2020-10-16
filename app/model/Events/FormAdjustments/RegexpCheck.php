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

    /** @var mixed */
    private $field;
    /** @var mixed */
    private $message;
    /** @var mixed */
    private $pattern;

    /**
     * RegexpCheck constructor.
     * @param string $field
     * @param string $message
     * @param string $pattern
     */
    public function __construct($field, $message, $pattern) {
        $this->field = $field;
        $this->message = $message;
        $this->pattern = $pattern;
    }

    protected function innerAdjust(Form $form, Machine $machine, Holder $holder): void {
        $controls = $this->getControl($this->field);
        if (!$controls) {
            return;
        }
        foreach ($controls as $control) {
            $control->addRule(function (IControl $control): bool {
                return (bool)Strings::match($control->getValue(), $this->pattern);
            }, $this->message);
        }
    }
}

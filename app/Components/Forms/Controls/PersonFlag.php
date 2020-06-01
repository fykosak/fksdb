<?php

namespace FKSDB\Components\Forms\Controls;

use Nette\Forms\Controls\SelectBox;
use Nette\Utils\Html;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class PersonFlag extends SelectBox {

    public const FLAG_YES = 'flag-yes';
    // It's necessary that value for FLAG_NO cannot coerce to false/null.
    public const FLAG_NO = 'flag-no';

    /**
     * Should be self::FLAG_* values on output?
     * @var bool
     */
    private $useExplicitValues = false;

    /**
     * PersonFlag constructor.
     * @param null $label
     */
    public function __construct($label = NULL) {
        $items = [
            self::FLAG_YES => _('Ano'),
            self::FLAG_NO => _('Ne'),
        ];
        parent::__construct($label, $items);
        $this->setPrompt('–');
    }

    /**
     * @return bool|float|int|mixed|string|null
     */
    public function getValue() {
        if ($this->useExplicitValues) {
            return parent::getValue();
        }

        switch ($this->value) {
            case self::FLAG_YES:
                return true;
            case self::FLAG_NO:
                return false;
            default:
                return null;
        }
    }

    /**
     * @param $value
     * @return static
     */
    public function setValue($value) {
        if ($value === true || $value === '1' || $value === 1) {
            parent::setValue(self::FLAG_YES);
        } elseif ($value === false || $value === '0' || $value === 0) {
            parent::setValue(self::FLAG_NO);
        } else {
            parent::setValue($value);
        }
        return $this;
    }

    /**
     * @return Html
     */
    public function getControl() {
        $oldMapped = $this->useExplicitValues;
        $this->useExplicitValues = true;
        $control = parent::getControl();
        $this->useExplicitValues = $oldMapped;
        return $control;
    }
}

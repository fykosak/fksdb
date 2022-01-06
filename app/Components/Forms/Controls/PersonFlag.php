<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Controls;

use Nette\Forms\Controls\SelectBox;
use Nette\Utils\Html;

class PersonFlag extends SelectBox {

    public const FLAG_YES = 'flag-yes';
    // It's necessary that value for FLAG_NO cannot coerce to false/null.
    public const FLAG_NO = 'flag-no';

    /**
     * Should be self::FLAG_* values on output?
     * @var bool
     */
    private bool $useExplicitValues = false;

    /**
     * PersonFlag constructor.
     * @param null $label
     */
    public function __construct($label = null) {
        $items = [
            self::FLAG_YES => _('Yes'),
            self::FLAG_NO => _('No'),
        ];
        parent::__construct($label, $items);
        $this->setPrompt('–');
    }

    /**
     * @return bool|int|string|null
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
     * @param mixed $value
     * @return static
     */
    public function setValue($value): self {
        if ($value === true || $value === '1' || $value === 1) {
            parent::setValue(self::FLAG_YES);
        } elseif ($value === false || $value === '0' || $value === 0) {
            parent::setValue(self::FLAG_NO);
        } else {
            parent::setValue($value);
        }
        return $this;
    }

    public function getControl(): Html {
        $oldMapped = $this->useExplicitValues;
        $this->useExplicitValues = true;
        $control = parent::getControl();
        $this->useExplicitValues = $oldMapped;
        return $control;
    }
}

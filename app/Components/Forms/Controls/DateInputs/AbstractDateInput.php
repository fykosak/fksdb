<?php

namespace FKSDB\Components\Forms\Controls\DateInputs;

use Nette\Utils\DateTime;
use Nette\Forms\Controls\TextInput;
use Nette\Utils\Html;

/**
 * Class AbstractDateInput
 * @author Michal Červeňák <miso@fykos.cz>
 * @property \DateTimeInterface $value
 */
abstract class AbstractDateInput extends TextInput {

    protected string $format;

    /**
     * AbstractDateInput constructor.
     * @param string $type
     * @param string $format
     * @param null $label
     * @param null $maxLength
     */
    public function __construct(string $type, string $format, $label = null, $maxLength = null) {
        $this->format = $format;
        parent::__construct($label, $maxLength);
        $this->setHtmlType($type);
    }

    public function getControl(): Html {
        $control = parent::getControl();
        if ($this->value) {
            $control->value = $this->value->format($this->format);
        }
        return $control;
    }

    /**
     * @param string|\DateTimeInterface|\DateInterval $value
     * @return static
     * @throws \Exception
     */
    public function setValue($value): self {
        if ($value instanceof \DateTimeInterface) {
            $this->value = $value;
        } elseif ($value instanceof \DateInterval) {
            $this->value = (new DateTime())->setTime($value->h, $value->m, $value->s);
        } elseif (is_string($value)) {
            $this->value = DateTime::from($value);
        } else {
            $this->value = null;
        }
        return $this;
    }
}

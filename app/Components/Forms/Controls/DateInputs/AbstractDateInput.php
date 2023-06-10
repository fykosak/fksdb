<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Controls\DateInputs;

use Nette\Utils\DateTime;
use Nette\Forms\Controls\TextInput;
use Nette\Utils\Html;

abstract class AbstractDateInput extends TextInput
{

    protected string $format;

    /**
     * AbstractDateInput constructor.
     * @param string|Html|null $label
     */
    public function __construct(string $type, string $format, ?string $label = null)
    {
        $this->format = $format;
        parent::__construct($label);
        $this->setHtmlType($type);
    }

    public function getControl(): Html
    {
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
    public function setValue($value): self
    {
        if ($value instanceof \DateTimeInterface) {
            $this->value = $value;
        } elseif ($value instanceof \DateInterval) {
            $this->value = (new DateTime())->setTime($value->h, $value->m, $value->s);
        } elseif (is_string($value) && $value !== '') {
            $this->value = DateTime::from($value);
        } else {
            $this->value = null;
        }
        return $this;
    }
}

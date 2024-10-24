<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Controls\DateInputs;

use Nette\Forms\Controls\TextInput;
use Nette\Utils\DateTime;

abstract class AbstractDateInput extends TextInput
{
    protected string $format;

    public function __construct(string $type, string $format, ?string $label = null)
    {
        $this->format = $format;
        parent::__construct($label);
        $this->setHtmlType($type);
    }

    /**
     * @param string|\DateTimeInterface|\DateInterval|null $value
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
        if ($this->value) {
            $this->rawValue = $this->value->format($this->format);
        } else {
            $this->rawValue = null;
        }
        return $this;
    }
}

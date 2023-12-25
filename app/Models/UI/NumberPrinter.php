<?php

declare(strict_types=1);

namespace FKSDB\Models\UI;

use Nette\Utils\Html;

class NumberPrinter
{
    public const NULL_VALUE_NOT_SET = 'notSet';
    public const NULL_VALUE_INF = 'infinite';
    public const NULL_VALUE_ZERO = 'zero';

    private ?string $nullValueMode;
    private ?string $prefix;
    private ?string $suffix;
    private int $decimal;

    public function __construct(
        ?string $prefix,
        ?string $suffix,
        int $decimal = 0,
        ?string $nullValueMode = null
    ) {
        $this->nullValueMode = $nullValueMode;
        $this->prefix = $prefix;
        $this->suffix = $suffix;
        $this->decimal = $decimal;
    }

    /**
     * @param int|float|null $value
     */
    public function __invoke($value): Html
    {
        if (\is_null($value)) {
            switch ($this->nullValueMode) {
                default:
                case self::NULL_VALUE_NOT_SET:
                    return NotSetBadge::getHtml();
                case self::NULL_VALUE_INF:
                    return Html::el('span')->addHtml('&#8734;');
                case self::NULL_VALUE_ZERO:
                    return Html::el('span')->addHtml($this->format(0.0));
            }
        }
        return Html::el('span')->addHtml($this->format((float)$value));
    }

    private function format(float $number): string
    {
        $text = isset($this->prefix) ? ($this->prefix . '&#8287;') : '';
        $text .= number_format(
            $number,
            $this->decimal,
            localeconv()['decimal_point'],
            localeconv()['thousands_sep']
        );
        $text .= isset($this->suffix) ? ('&#8287;' . $this->suffix) : '';
        return $text;
    }
}

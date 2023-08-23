<?php

declare(strict_types=1);

namespace FKSDB\Models\ValuePrinters;

use FKSDB\Components\Badges\NotSetBadge;
use Nette\Utils\Html;

/**
 * @phpstan-extends ValuePrinter<int|float>
 */
class NumberPrinter extends ValuePrinter
{
    public const NULL_VALUE_NOT_SET = 'notSet';
    public const NULL_VALUE_INF = 'infinite';
    public const NULL_VALUE_ZERO = 'zero';

    private ?string $nullValueMode;
    private ?string $prefix;
    private ?string $suffix;
    private int $decimal;
    private ?string $decimalSeparator;
    private ?string $thousandsSeparator;

    public function __construct(
        ?string $prefix,
        ?string $suffix,
        int $decimal = 0,
        ?string $nullValueMode = null,
        ?string $decimalSeparator = null,
        ?string $thousandsSeparator = null
    ) {
        $this->nullValueMode = $nullValueMode;
        $this->prefix = $prefix;
        $this->suffix = $suffix;
        $this->decimal = $decimal;
        $this->thousandsSeparator = $thousandsSeparator;
        $this->decimalSeparator = $decimalSeparator;
    }

    /**
     * @param int|float $value
     */
    protected function getHtml($value): Html
    {
        return Html::el('span')->addHtml($this->format((float)$value));
    }

    private function format(float $number): string
    {
        $text = isset($this->prefix) ? ($this->prefix . '&#8287;') : '';
        $text .= number_format(
            $number,
            $this->decimal,
            $this->decimalSeparator ?? localeconv()['decimal_point'],
            $this->thousandsSeparator ?? localeconv()['thousands_sep']
        );
        $text .= isset($this->suffix) ? ('&#8287;' . $this->suffix) : '';
        return $text;
    }

    protected function getEmptyValueHtml(): Html
    {
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
}

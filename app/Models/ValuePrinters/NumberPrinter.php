<?php

declare(strict_types=1);

namespace FKSDB\Models\ValuePrinters;

use FKSDB\Components\Badges\NotSetBadge;
use Nette\Utils\Html;

class NumberPrinter extends AbstractValuePrinter {
    public const NULL_VALUE_NOT_SET = 'notSet';
    public const NULL_VALUE_INF = 'infinite';
    public const NULL_VALUE_ZERO = 'zero';

    private ?string $nullValueMode;

    private ?string $prefix;

    private ?string $suffix;

    private int $decimal;

    public function __construct(?string $prefix, ?string $suffix, int $decimal = 2, ?string $nullValueMode = self::NULL_VALUE_NOT_SET) {
        $this->nullValueMode = $nullValueMode;
        $this->prefix = $prefix;
        $this->suffix = $suffix;
        $this->decimal = $decimal;
    }

    /**
     * @param int|float $value
     */
    protected function getHtml($value): Html {
        return Html::el('span')->addHtml($this->format($value));
    }

    private function format(int $number): string {
        $text = isset($this->prefix) ? ($this->prefix . '&#8287;') : '';
        $text .= number_format($number, 0, ',', '&#8287;');
        $text .= isset($this->suffix) ? ('&#8287;' . $this->suffix) : '';
        return $text;
    }

    protected function getEmptyValueHtml(): Html {
        switch ($this->nullValueMode) {
            default:
            case self::NULL_VALUE_NOT_SET:
                return NotSetBadge::getHtml();
            case self::NULL_VALUE_INF:
                return Html::el('span')->addHtml('&#8734;');
            case self::NULL_VALUE_ZERO:
                return Html::el('span')->addHtml($this->format(0));
        }
    }
}

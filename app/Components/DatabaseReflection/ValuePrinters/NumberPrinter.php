<?php

namespace FKSDB\Components\DatabaseReflection\ValuePrinters;

use FKSDB\Components\Controls\Badges\NotSetBadge;
use Nette\Utils\Html;

/**
 * Class NumberPrinter
 * @author Michal Červeňák <miso@fykos.cz>
 */
class NumberPrinter extends AbstractValuePrinter {
    const NULL_VALUE_NOT_SET = 'notSet';
    const NULL_VALUE_INF = 'infinite';
    const NULL_VALUE_ZERO = 'zero';
    /**
     * @var string
     */
    private $nullValueMode;
    /**
     * @var string|null
     */
    private $prefix;
    /**
     * @var string|null
     */
    private $suffix;
    /**
     * @var int
     */
    private $decimal;

    /**
     * NumberPrinter constructor.
     * @param string|null $prefix
     * @param string|null $suffix
     * @param int $decimal
     * @param string $nullValueMode
     */
    public function __construct($prefix, $suffix, int $decimal = 2, $nullValueMode = self::NULL_VALUE_NOT_SET) {
        $this->nullValueMode = $nullValueMode;
        $this->prefix = $prefix;
        $this->suffix = $suffix;
        $this->decimal = $decimal;
    }

    /**
     * @param int|float $value
     * @return Html
     */
    protected function getHtml($value): Html {
        return Html::el('span')->addHtml($this->format($value));
    }

    private function format(int $number): string {
        $text = $this->prefix ? ($this->prefix . '&#8287;') : '';
        $text .= number_format($number, 0, ',', '&#8287;');
        $text .= $this->suffix ? ('&#8287;' . $this->suffix) : '';
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

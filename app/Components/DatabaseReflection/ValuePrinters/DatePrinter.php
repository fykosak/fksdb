<?php

namespace FKSDB\Components\DatabaseReflection\ValuePrinters;

use FKSDB\Components\Controls\Helpers\Badges\NotSetBadge;
use Nette\Utils\DateTime;
use Nette\Utils\Html;
use function is_null;

/**
 * Class DatePrinter
 * @package FKSDB\Components\DatabaseReflection\ValuePrinters
 */
class DatePrinter extends AbstractValuePrinter {
    protected $format = 'c';

    /**
     * DatePrinter constructor.
     * @param string|null $format
     */
    public function __construct(string $format = 'c') {
        $this->format = $format;
    }

    /**
     * @param DateTime|null $value
     * @return Html
     */
    protected function getHtml($value): Html {
        if (is_null($value)) {
            return NotSetBadge::getHtml();
        } else {
            return Html::el('span')->addText($value->format($this->format));
        }
    }
}

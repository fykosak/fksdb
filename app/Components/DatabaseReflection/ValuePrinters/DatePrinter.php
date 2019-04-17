<?php

namespace FKSDB\Components\DatabaseReflection\ValuePrinters;

use FKSDB\Components\Controls\Helpers\Badges\NotSetBadge;
use Nette\DateTime;
use Nette\Utils\Html;

/**
 * Class DatePrinter
 * @package FKSDB\Components\DatabaseReflection\ValuePrinters
 */
class DatePrinter extends AbstractValuePrinter {
    protected $format = 'c';

    /**
     * @param DateTime|null $value
     * @return Html
     */
    protected function getHtml($value): Html {
        if (\is_null($value)) {
            return NotSetBadge::getHtml();
        } else {
            return Html::el('span')->addText($value->format($this->format));
        }
    }

    /**
     * @param $value
     * @param string $format
     * @return Html
     */
    public function __invoke($value, $format = 'c'): Html {
        $this->format = $format;
        return parent::__invoke($value);
    }
}

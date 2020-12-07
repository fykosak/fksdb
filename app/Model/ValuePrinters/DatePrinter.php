<?php

namespace FKSDB\Model\ValuePrinters;

use Nette\Utils\DateTime;
use Nette\Utils\Html;

/**
 * Class DatePrinter
 * @author Michal Červeňák <miso@fykos.cz>
 */
class DatePrinter extends AbstractValuePrinter {

    protected string $format;

    public function __construct(string $format = 'c') {
        $this->format = $format;
    }

    /**
     * @param DateTime $value
     * @return Html
     */
    protected function getHtml($value): Html {
        return Html::el('span')->addText($value->format($this->format));
    }
}

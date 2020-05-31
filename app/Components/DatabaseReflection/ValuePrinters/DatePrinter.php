<?php

namespace FKSDB\Components\DatabaseReflection\ValuePrinters;

use FKSDB\Components\Controls\Badges\NotSetBadge;
use Nette\Utils\DateTime;
use Nette\Utils\Html;

/**
 * Class DatePrinter
 * @author Michal Červeňák <miso@fykos.cz>
 */
class DatePrinter extends AbstractValuePrinter {
    /** @var string|null */
    protected $format = 'c';

    /**
     * DatePrinter constructor.
     * @param string|null $format
     */
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

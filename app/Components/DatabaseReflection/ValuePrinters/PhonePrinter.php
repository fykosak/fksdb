<?php

namespace FKSDB\Components\DatabaseReflection\ValuePrinters;

use FKSDB\Components\Controls\Helpers\Badges\NotSetBadge;
use FKSDB\Components\Controls\PhoneNumber\PhoneNumberFactory;
use Nette\Utils\Html;

/**
 * Class PhonePrinter
 * @package FKSDB\Components\DatabaseReflection\ValuePrinters
 */
class PhonePrinter extends AbstractValuePrinter {
    /**
     * @param $value
     * @return Html
     */
    public function getHtml($value): Html {
        if (\is_null($value)) {
            return NotSetBadge::getHtml();
        } else {
            return PhoneNumberFactory::format($value);
        }
    }
}

<?php


namespace FKSDB\Components\DatabaseReflection\ValuePrinters;

use FKSDB\Components\Controls\Helpers\Badges\NotSetBadge;
use Nette\Utils\Html;

/**
 * Class EmailPrinter
 * @package FKSDB\Components\DatabaseReflection\ValuePrinters
 */
class EmailPrinter extends AbstractValuePrinter {
    /**
     * @param null $value
     * @return Html
     */
    protected function getHtml($value): Html {
        if (\is_null($value)) {
            return NotSetBadge::getHtml();
        } else {
            list('address' => $address) = \mailparse_rfc822_parse_addresses($value)[0];
            return Html::el('a')->addAttributes(['href' => 'mailto:' . $address])->addText($value);
        }
    }
}

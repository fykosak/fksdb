<?php


namespace FKSDB\Components\DatabaseReflection\ValuePrinters;

use FKSDB\Components\Controls\Badges\NotSetBadge;
use Nette\Utils\Html;

/**
 * Class EmailPrinter
 * @author Michal Červeňák <miso@fykos.cz>
 */
class EmailPrinter extends AbstractValuePrinter {
    /**
     * @param null $value
     * @return Html
     */
    protected function getHtml($value): Html {
        return Html::el('a')->addAttributes(['href' => 'mailto:' . $value])->addText($value);
    }
}

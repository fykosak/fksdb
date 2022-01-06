<?php

declare(strict_types=1);

namespace FKSDB\Models\ValuePrinters;

use Nette\Utils\Html;

class EmailPrinter extends AbstractValuePrinter {
    /**
     * @param null $value
     */
    protected function getHtml($value): Html {
        return Html::el('a')->addAttributes(['href' => 'mailto:' . $value])->addText($value);
    }
}

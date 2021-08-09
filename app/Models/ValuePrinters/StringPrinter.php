<?php

declare(strict_types=1);

namespace FKSDB\Models\ValuePrinters;

use Nette\Utils\Html;

class StringPrinter extends AbstractValuePrinter
{
    /***
     * @param string $value
     * @return Html
     */
    protected function getHtml($value): Html
    {
        return Html::el('span')->addText($value);
    }
}

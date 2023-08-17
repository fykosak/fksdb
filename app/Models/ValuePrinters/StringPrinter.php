<?php

declare(strict_types=1);

namespace FKSDB\Models\ValuePrinters;

use Nette\Utils\Html;

/**
 * @phpstan-extends ValuePrinter<string>
 */
class StringPrinter extends ValuePrinter
{
    /***
     * @param string $value
     */
    protected function getHtml($value): Html
    {
        return Html::el('span')->addText($value);
    }
}

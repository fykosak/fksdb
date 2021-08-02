<?php

namespace FKSDB\Models\ValuePrinters;

use Nette\Utils\Html;

class HashPrinter extends AbstractValuePrinter
{
    /**
     * @param string $value
     * @return Html
     */
    protected function getHtml($value): Html
    {
        return Html::el('span')->addAttributes(['class' => 'badge badge-success'])->addText(_('Is set'));
    }
}

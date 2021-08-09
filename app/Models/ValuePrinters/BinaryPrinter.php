<?php

declare(strict_types=1);

namespace FKSDB\Models\ValuePrinters;

use Nette\Utils\Html;

class BinaryPrinter extends AbstractValuePrinter
{
    /**
     * @param int|bool $value
     * @return Html
     */
    protected function getHtml($value): Html
    {
        if ($value) {
            return Html::el('span')->addAttributes(['class' => 'fas fa-check text-success']);
        } else {
            return Html::el('span')->addAttributes(['class' => 'fas fa-times text-danger']);
        }
    }
}

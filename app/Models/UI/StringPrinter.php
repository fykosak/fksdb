<?php

declare(strict_types=1);

namespace FKSDB\Models\UI;

use Nette\Utils\Html;

class StringPrinter
{
    public static function getHtml(?string $value): Html
    {
        if (\is_null($value)) {
            return NotSetBadge::getHtml();
        }
        return Html::el('span')->addText($value);
    }
}

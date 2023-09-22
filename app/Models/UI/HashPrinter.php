<?php

declare(strict_types=1);

namespace FKSDB\Models\UI;

use Nette\Utils\Html;

class HashPrinter
{
    public static function getHtml(?string $value): Html
    {
        if (\is_null($value)) {
            return NotSetBadge::getHtml();
        }
        return Html::el('span')->addAttributes(['class' => 'me-1 badge bg-success'])->addText(_('Is set'));
    }
}

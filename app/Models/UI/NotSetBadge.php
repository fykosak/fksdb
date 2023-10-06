<?php

declare(strict_types=1);

namespace FKSDB\Models\UI;

use Nette\Utils\Html;

class NotSetBadge
{
    public static function getHtml(): Html
    {
        return Html::el('span')->addAttributes(['class' => 'me-1 badge bg-warning'])->addText(_('Not set'));
    }
}

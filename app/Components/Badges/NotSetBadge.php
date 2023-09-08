<?php

declare(strict_types=1);

namespace FKSDB\Components\Badges;

use Nette\Utils\Html;

class NotSetBadge
{
    public static function getHtml(): Html
    {
        return Html::el('span')->addAttributes(['class' => 'badge bg-warning'])->addText(_('Not set'));
    }
}

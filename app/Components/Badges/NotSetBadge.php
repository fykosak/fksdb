<?php

declare(strict_types=1);

namespace FKSDB\Components\Badges;

use Nette\Utils\Html;

class NotSetBadge extends Badge
{

    public static function getHtml(...$args): Html
    {
        return Html::el('span')->addAttributes(['class' => 'badge badge-warning'])->addText(_('Not set'));
    }
}

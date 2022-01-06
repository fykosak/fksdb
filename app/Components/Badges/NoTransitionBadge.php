<?php

declare(strict_types=1);

declare(strict_types=1);

namespace FKSDB\Components\Badges;

use Nette\Utils\Html;

class NoTransitionBadge extends Badge
{
    public static function getHtml(...$args): Html
    {
        return Html::el('span')
            ->addAttributes(['class' => 'badge badge-warning'])
            ->addText(_('No transitions available'));
    }
}

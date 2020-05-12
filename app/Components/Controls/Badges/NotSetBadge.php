<?php

namespace FKSDB\Components\Controls\Badges;

use Nette\Utils\Html;

/**
 *
 * @package FKSDB\Components\Controls\Stalking\Helpers
 *
 */
class NotSetBadge extends Badge {
    /**
     * @param array $args
     * @return Html
     */
    public static function getHtml(...$args): Html {
        return Html::el('span')->addAttributes(['class' => 'badge badge-warning'])->addText(_('Not set'));
    }
}

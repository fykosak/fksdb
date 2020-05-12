<?php

namespace FKSDB\Components\Controls\Badges;

use Nette\Utils\Html;

/**
 *
 * @package FKSDB\Components\Controls\Stalking\Helpers
 */
class NoRecordsBadge extends Badge {
    /**
     * @param mixed ...$args
     * @return Html
     */
    public static function getHtml(...$args): Html {
        return Html::el('span')->addAttributes(['class' => 'badge-warning badge'])->setText(_('No records'));
    }
}

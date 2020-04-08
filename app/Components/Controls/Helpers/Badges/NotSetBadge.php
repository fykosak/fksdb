<?php

namespace FKSDB\Components\Controls\Helpers\Badges;

use Nette\Utils\Html;

/**
 *
 * @package FKSDB\Components\Controls\Stalking\Helpers
 *
 */
class NotSetBadge {
    /**
     * @return Html
     */
    public static function getHtml(): Html {
        return Html::el('span')->addAttributes(['class' => 'badge badge-warning'])->addText(_('Not set'));
    }
}

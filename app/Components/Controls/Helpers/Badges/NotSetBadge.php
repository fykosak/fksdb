<?php

namespace FKSDB\Components\Controls\Helpers\Badges;

use Nette\Templating\FileTemplate;
use Nette\Utils\Html;

/**
 *
 * @package FKSDB\Components\Controls\Stalking\Helpers
 * @property FileTemplate $template
 */
class NotSetBadge {
    /**
     * @return Html
     */
    public static function getHtml(): Html {
        return Html::el('span')->addAttributes(['class' => 'badge badge-warning'])->addText(_('Not set'));
    }
}

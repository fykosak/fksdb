<?php

namespace FKSDB\Components\Controls\Badges;

use FKSDB\Components\Controls\BaseComponent;
use Nette\Templating\FileTemplate;
use Nette\Utils\Html;

/**
 * Class Badge
 * @package FKSDB\Components\Controls\Badges
 * @property-read FileTemplate $template
 */
abstract class Badge extends BaseComponent {

    /**
     * @param mixed ...$args
     * @return Html
     */
    abstract public static function getHtml(...$args): Html;

    /**
     * @param mixed ...$args
     */
    public function render(...$args) {
        $this->template->html = static::getHtml(...$args);
        $this->template->setFile(__DIR__ . '/layout.latte');
        $this->template->render();
    }
}

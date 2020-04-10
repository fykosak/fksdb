<?php

namespace FKSDB\Components\Controls\Badges;

use Nette\Application\UI\Control;
use Nette\Utils\Html;

/**
 * Class Badge
 * @package FKSDB\Components\Controls\Badges
 */
abstract class Badge extends Control {
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

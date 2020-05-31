<?php

namespace FKSDB\Components\Controls\Badges;

use FKSDB\Components\Controls\BaseComponent;
use Nette\Utils\Html;

/**
 * Class Badge
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class Badge extends BaseComponent {

    abstract public static function getHtml(...$args): Html;

    public function render(...$args): void {
        $this->template->html = static::getHtml(...$args);
        $this->template->setFile(__DIR__ . '/layout.latte');
        $this->template->render();
    }
}

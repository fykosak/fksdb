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

    final public function render(...$args): void {
        $this->template->html = static::getHtml(...$args);
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.latte');
    }
}

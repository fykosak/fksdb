<?php

declare(strict_types=1);

namespace FKSDB\Components\Badges;

use FKSDB\Components\Controls\BaseComponent;
use Nette\Utils\Html;

abstract class Badge extends BaseComponent
{

    abstract public static function getHtml(...$args): Html;

    final public function render(...$args): void
    {
        $this->template->html = static::getHtml(...$args);
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.latte');
    }
}

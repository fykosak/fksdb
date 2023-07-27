<?php

declare(strict_types=1);

namespace FKSDB\Components\Badges;

use Fykosak\Utils\BaseComponent\BaseComponent;
use Nette\Utils\Html;

abstract class Badge extends BaseComponent
{
    /**
     * @phpstan-param mixed $args
     */
    abstract public static function getHtml(...$args): Html;

    /**
     * @phpstan-param mixed $args
     */
    final public function render(...$args): void
    {
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.latte', ['html' => static::getHtml(...$args)]);
    }
}

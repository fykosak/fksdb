<?php

declare(strict_types=1);

namespace FKSDB\Components\Badges;

use Fykosak\Utils\BaseComponent\BaseComponent;
use Nette\Utils\Html;

/**
 * @template ArgType
 */
abstract class Badge extends BaseComponent
{
    /**
     * @phpstan-param ArgType $args
     */
    abstract public static function getHtml(...$args): Html;

    /**
     * @phpstan-param ArgType $args
     */
    final public function render(...$args): void
    {
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.latte', ['html' => static::getHtml(...$args)]);
    }
}

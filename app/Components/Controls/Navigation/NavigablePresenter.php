<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Navigation;

use Fykosak\Utils\UI\Title;
use Nette\Application\IPresenter;

/**
 * In order to Breadcrumbs control work properly,
 * presenter must implement this interface and in the beforeRender method
 * call control's method setBacklink.
 *
 * @note navigable == splavný :-)
 */
interface NavigablePresenter extends IPresenter
{

    /**
     * Should set persistent parameter identifying the backlink.
     * @return string|null  original value of the backlink parameter
     */
    public function setBackLink(string $backLink): ?string;

    /**
     * Returns title object of the current view.
     */
    public function getTitle(): Title;

    /**
     * Publish access of the protecetd static method.
     */
    public static function publicFormatActionMethod(string $action): string;

    public static function getBackLinkParamName(): string;
}

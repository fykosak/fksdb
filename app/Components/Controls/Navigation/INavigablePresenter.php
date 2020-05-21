<?php

namespace FKSDB\Components\Controls\Navigation;

use FKSDB\UI\PageTitle;
use Nette\Application\IPresenter;

/**
 * In order to Breadcrumbs control work properly,
 * presenter must implement this interface and in the beforeRender method
 * call control's method setBacklink.
 *
 * @note navigable == splavný :-)
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
interface INavigablePresenter extends IPresenter {

    /**
     * Should set persistent parameter identifying the backlink.
     *
     * @param string $backLink
     * @return string|null  original value of the backlink parameter
     */
    public function setBackLink($backLink);

    /**
     * Returns title of the current view.
     *
     * @return PageTitle
     */
    public function getTitle(): PageTitle;

    /**
     * Publish access of the protecetd static method.
     *
     * @param string $action
     * @return string
     */
    public static function publicFormatActionMethod(string $action): string;

    /**
     * @return string
     */
    public static function getBackLinkParamName(): string;
}

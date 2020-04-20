<?php

namespace FKSDB\Components\Controls\Navigation;

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
     * @return array
     */
    public function getTitle(): array;

    /**
     * Publish access of the protecetd static method.
     *
     * @param string $action
     * @return string
     */
    public static function publicFormatActionMethod($action);

    /**
     * @return string
     */
    public static function getBacklinkParamName();
}



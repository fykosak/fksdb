<?php

namespace FKS\Components\Controls\Navigation;

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
     * @param string $backlink
     * @return string|null  original value of the backlink parameter
     */
    public function setBacklink($backlink);

    /**
     * Returns title of the current view.
     * 
     * @return string
     */
    public function getTitle();

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

?>

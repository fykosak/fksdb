<?php

namespace FKSDB;

use FKSDB\Components\Controls\Loaders\JavaScript\JavaScriptLoader;
use FKSDB\Components\Controls\Loaders\Stylesheet\StylesheetLoader;

/**
 * Class CollectorPresenter
 * @package FKSDB
 */
trait CollectorPresenterTrait {

    /*	 * ******************************
     * Loading assets
     * ****************************** */

    /**
     * @return JavaScriptLoader
     */
    protected function createComponentJsLoader(): JavaScriptLoader {
        return new JavaScriptLoader();
    }

    /**
     * @return StylesheetLoader
     */
    protected function createComponentCssLoader(): StylesheetLoader {
        return new StylesheetLoader();
    }

    /*	 * ******************************
     * IJavaScriptCollector
     * ****************************** */
    /**
     * @param string $file
     */
    public function registerJSFile(string $file) {
        /** @var JavaScriptLoader $component */
        $component = $this->getComponent('jsLoader');
        $component->addFile($file);
    }

    /**
     * @param string $code
     * @param string $tag
     */
    public function registerJSCode(string $code, string $tag = null) {
        /** @var JavaScriptLoader $component */
        $component = $this->getComponent('jsLoader');
        $component->addInline($code, $tag);
    }

    /**
     * @param string $tag
     */
    public function unregisterJSCode(string $tag) {
        /** @var JavaScriptLoader $component */
        $component = $this->getComponent('jsLoader');
        $component->removeInline($tag);
    }

    /**
     * @param string $file
     */
    public function unregisterJSFile(string $file) {
        /** @var JavaScriptLoader $component */
        $component = $this->getComponent('jsLoader');
        $component->removeFile($file);
    }

    /*	 * ******************************
     * IStylesheetCollector
     * ****************************** */
    /**
     * @param string $file
     * @param array $media
     */
    public function registerStylesheetFile(string $file, array $media = []) {
        /** @var StylesheetLoader $component */
        $component = $this->getComponent('cssLoader');
        $component->addFile($file, $media);
    }

    /**
     * @param string $file
     * @param array $media
     */
    public function unregisterStylesheetFile(string $file, array $media = []) {
        /** @var StylesheetLoader $component */
        $component = $this->getComponent('cssLoader');
        $component->removeFile($file, $media);
    }
}

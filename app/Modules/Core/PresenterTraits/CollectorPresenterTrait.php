<?php

namespace FKSDB\Modules\Core\PresenterTraits;

use FKSDB\Components\Controls\Loaders\JavaScript\JavaScriptLoader;
use FKSDB\Components\Controls\Loaders\Stylesheet\StylesheetLoader;

/**
 * Trait CollectorPresenterTrait
 * @author Michal Červeňák <miso@fykos.cz>
 * @author Michal Koutny
 */
trait CollectorPresenterTrait {

    /*	 * ******************************
     * Loading assets
     * ****************************** */

    protected function createComponentJsLoader(): JavaScriptLoader {
        return new JavaScriptLoader();
    }

    protected function createComponentCssLoader(): StylesheetLoader {
        return new StylesheetLoader();
    }

    /*	 * ******************************
     * IJavaScriptCollector
     * ****************************** */
    /**
     * @param string $file
     * @return void
     */
    public function registerJSFile(string $file) {
        /** @var JavaScriptLoader $component */
        $component = $this->getComponent('jsLoader');
        $component->addFile($file);
    }

    /**
     * @param string $code
     * @param string|null $tag
     * @return void
     */
    public function registerJSCode(string $code, string $tag = null) {
        /** @var JavaScriptLoader $component */
        $component = $this->getComponent('jsLoader');
        $component->addInline($code, $tag);
    }

    /**
     * @param string $tag
     * @return void
     */
    public function unregisterJSCode(string $tag) {
        /** @var JavaScriptLoader $component */
        $component = $this->getComponent('jsLoader');
        $component->removeInline($tag);
    }

    /**
     * @param string $file
     * @return void
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
     * @return void
     */
    public function registerStylesheetFile(string $file, array $media = []) {
        /** @var StylesheetLoader $component */
        $component = $this->getComponent('cssLoader');
        $component->addFile($file, $media);
    }

    /**
     * @param string $file
     * @param array $media
     * @return void
     */
    public function unregisterStylesheetFile(string $file, array $media = []) {
        /** @var StylesheetLoader $component */
        $component = $this->getComponent('cssLoader');
        $component->removeFile($file, $media);
    }
}

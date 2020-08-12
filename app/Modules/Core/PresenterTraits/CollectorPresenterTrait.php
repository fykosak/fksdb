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
    public function registerJSFile(string $file): void {
        /** @var JavaScriptLoader $component */
        $component = $this->getComponent('jsLoader');
        $component->addFile($file);
    }

    public function registerJSCode(string $code, ?string $tag = null): void {
        /** @var JavaScriptLoader $component */
        $component = $this->getComponent('jsLoader');
        $component->addInline($code, $tag);
    }

    public function unregisterJSCode(string $tag): void {
        /** @var JavaScriptLoader $component */
        $component = $this->getComponent('jsLoader');
        $component->removeInline($tag);
    }

    public function unregisterJSFile(string $file): void {
        /** @var JavaScriptLoader $component */
        $component = $this->getComponent('jsLoader');
        $component->removeFile($file);
    }

    /*	 * ******************************
     * IStylesheetCollector
     * ****************************** */

    public function registerStylesheetFile(string $file, array $media = []): void {
        /** @var StylesheetLoader $component */
        $component = $this->getComponent('cssLoader');
        $component->addFile($file, $media);
    }

    public function unregisterStylesheetFile(string $file, array $media = []): void {
        /** @var StylesheetLoader $component */
        $component = $this->getComponent('cssLoader');
        $component->removeFile($file, $media);
    }
}

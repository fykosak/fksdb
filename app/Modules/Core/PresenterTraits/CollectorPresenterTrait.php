<?php

namespace FKSDB\Modules\Core\PresenterTraits;

use FKSDB\Components\Controls\Loaders\JavaScript\JavaScriptLoaderComponent;
use FKSDB\Components\Controls\Loaders\Stylesheet\StylesheetLoaderComponent;

/**
 * Trait CollectorPresenterTrait
 * @author Michal Červeňák <miso@fykos.cz>
 * @author Michal Koutny
 */
trait CollectorPresenterTrait {

    /*	 * ******************************
     * Loading assets
     * ****************************** */

    protected function createComponentJsLoader(): JavaScriptLoaderComponent {
        return new JavaScriptLoaderComponent($this->getContext());
    }

    protected function createComponentCssLoader(): StylesheetLoaderComponent {
        return new StylesheetLoaderComponent($this->getContext());
    }

    /*	 * ******************************
     * IJavaScriptCollector
     * ****************************** */
    public function registerJSFile(string $file): void {
        /** @var JavaScriptLoaderComponent $component */
        $component = $this->getComponent('jsLoader');
        $component->addFile($file);
    }

    public function registerJSCode(string $code, ?string $tag = null): void {
        /** @var JavaScriptLoaderComponent $component */
        $component = $this->getComponent('jsLoader');
        $component->addInline($code, $tag);
    }

    public function unregisterJSCode(string $tag): void {
        /** @var JavaScriptLoaderComponent $component */
        $component = $this->getComponent('jsLoader');
        $component->removeInline($tag);
    }

    public function unregisterJSFile(string $file): void {
        /** @var JavaScriptLoaderComponent $component */
        $component = $this->getComponent('jsLoader');
        $component->removeFile($file);
    }

    /*	 * ******************************
     * IStylesheetCollector
     * ****************************** */

    public function registerStylesheetFile(string $file, array $media = []): void {
        /** @var StylesheetLoaderComponent $component */
        $component = $this->getComponent('cssLoader');
        $component->addFile($file, $media);
    }

    public function unregisterStylesheetFile(string $file, array $media = []): void {
        /** @var StylesheetLoaderComponent $component */
        $component = $this->getComponent('cssLoader');
        $component->removeFile($file, $media);
    }
}

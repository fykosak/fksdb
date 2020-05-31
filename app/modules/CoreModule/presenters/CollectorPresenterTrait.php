<?php

namespace FKSDB;

use FKSDB\Components\Controls\Loaders\JavaScript\JavaScriptLoader;
use FKSDB\Components\Controls\Loaders\Stylesheet\StylesheetLoader;

/**
 * Class CollectorPresenter
 * *
 */
trait CollectorPresenterTrait {

    protected function createComponentJsLoader(): JavaScriptLoader {
        return new JavaScriptLoader();
    }

    protected function createComponentCssLoader(): StylesheetLoader {
        return new StylesheetLoader();
    }

    public function registerJSFile(string $file): void {
        /** @var JavaScriptLoader $component */
        $component = $this->getComponent('jsLoader');
        $component->addFile($file);
    }

    public function unregisterJSFile(string $file): void {
        /** @var JavaScriptLoader $component */
        $component = $this->getComponent('jsLoader');
        $component->removeFile($file);
    }

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

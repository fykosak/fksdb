<?php

declare(strict_types=1);

declare(strict_types=1);

namespace FKSDB\Modules\Core\PresenterTraits;

use FKSDB\Components\Controls\Loaders\JavaScript\JavaScriptLoaderComponent;
use FKSDB\Components\Controls\Loaders\Stylesheet\StylesheetLoaderComponent;

trait CollectorPresenterTrait
{

    /* *******************************
     * Loading assets
     * ****************************** */

    public function registerJSFile(string $file): void
    {
        /** @var JavaScriptLoaderComponent $component */
        $component = $this->getComponent('jsLoader');
        $component->addFile($file);
    }

    public function registerJSCode(string $code, ?string $tag = null): void
    {
        /** @var JavaScriptLoaderComponent $component */
        $component = $this->getComponent('jsLoader');
        $component->addInline($code, $tag);
    }

    /* *******************************
     * IJavaScriptCollector
     * ****************************** */

    public function unregisterJSCode(string $tag): void
    {
        /** @var JavaScriptLoaderComponent $component */
        $component = $this->getComponent('jsLoader');
        $component->removeInline($tag);
    }

    public function unregisterJSFile(string $file): void
    {
        /** @var JavaScriptLoaderComponent $component */
        $component = $this->getComponent('jsLoader');
        $component->removeFile($file);
    }

    public function registerStylesheetFile(string $file, array $media = []): void
    {
        /** @var StylesheetLoaderComponent $component */
        $component = $this->getComponent('cssLoader');
        $component->addFile($file, $media);
    }

    public function unregisterStylesheetFile(string $file, array $media = []): void
    {
        /** @var StylesheetLoaderComponent $component */
        $component = $this->getComponent('cssLoader');
        $component->removeFile($file, $media);
    }

    /* *******************************
     * IStylesheetCollector
     * ****************************** */

    protected function createComponentJsLoader(): JavaScriptLoaderComponent
    {
        return new JavaScriptLoaderComponent($this->getContext());
    }

    protected function createComponentCssLoader(): StylesheetLoaderComponent
    {
        return new StylesheetLoaderComponent($this->getContext());
    }
}

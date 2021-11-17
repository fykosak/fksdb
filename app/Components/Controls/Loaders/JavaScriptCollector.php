<?php

namespace FKSDB\Components\Controls\Loaders;

interface JavaScriptCollector {

    /**
     * @param string $file path relative to webroot
     */
    public function registerJSFile(string $file): void;
}

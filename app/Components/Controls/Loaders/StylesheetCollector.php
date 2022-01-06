<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Loaders;

interface StylesheetCollector {

    /**
     * @param string $file path relative to webroot
     */
    public function registerStylesheetFile(string $file, array $media = ['all']): void;

    /**
     * @param string $file path relative to webroot
     */
    public function unregisterStylesheetFile(string $file, array $media = ['all']): void;
}

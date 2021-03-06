<?php

namespace FKSDB\Components\Controls\Loaders;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
interface StylesheetCollector {

    /**
     * @param string $file path relative to webroot
     * @param array $media
     */
    public function registerStylesheetFile(string $file, array $media = ['all']): void;

    /**
     * @param string $file path relative to webroot
     * @param array $media
     */
    public function unregisterStylesheetFile(string $file, array $media = ['all']): void;
}

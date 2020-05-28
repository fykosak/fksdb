<?php

namespace FKSDB\Application;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
interface IStylesheetCollector {

    /**
     * @param string $file path relative to webroot
     * @param array $media
     * @return void
     */
    public function registerStylesheetFile(string $file, array $media = ['all']);

    /**
     * @param string $file path relative to webroot
     * @param array $media
     * @return void
     */
    public function unregisterStylesheetFile(string $file, array $media = ['all']);
}

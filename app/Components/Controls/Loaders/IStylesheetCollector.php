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
     * @param $media array
     */
    public function registerStylesheetFile($file, $media = ['all']);

    /**
     * @param string $file path relative to webroot
     * @param $media array
     */
    public function unregisterStylesheetFile($file, $media = ['all']);
}


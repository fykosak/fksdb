<?php

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
interface IStylesheetCollector {

    /**
     * @param string $file path relative to webroot
     */
    public function registerStylesheetFile($file, $media = array('all'));

    /**
     * @param string $file path relative to webroot
     */
    public function unregisterStylesheetFile($file, $media = array('all'));
}


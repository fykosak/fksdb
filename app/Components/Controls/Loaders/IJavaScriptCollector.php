<?php

namespace FKSDB\Application;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
interface IJavaScriptCollector {

    /**
     * @param string $file path relative to webroot
     */
    public function registerJSFile(string $file): void;


    /**
     * @param string $file path relative to webroot
     */
    public function unregisterJSFile(string $file): void;

}

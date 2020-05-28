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
     * @return void
     */
    public function registerJSFile(string $file);

    /**
     * @param string $file path relative to webroot
     * @return void
     */
    public function unregisterJSFile(string $file);
}

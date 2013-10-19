<?php

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
interface IJavaScriptCollector {

    /**
     * @param string $file path relative to webroot
     */
    public function registerJSFile($file);

    /**
     * @param string $code JS code taht should be inserted into the page
     */
    public function registerJSCode($code);
}

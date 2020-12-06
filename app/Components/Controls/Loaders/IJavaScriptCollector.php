<?php

namespace FKSDB\Components\Controls\Loaders;

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
     *
     * @param string $code JS code taht should be inserted into the page
     * @param string|null $tag tag of the code for later reference
     * @deprecated Leads to eval for AJAX requests.
     */
    public function registerJSCode(string $code, string $tag = null): void;

    /**
     * @param string $file path relative to webroot
     */
    public function unregisterJSFile(string $file): void;

    /**
     *
     * @param string $tag code tag to be removed
     */
    public function unregisterJSCode(string $tag): void;
}

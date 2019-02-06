<?php

namespace FKSDB\Components\Controls\Loaders\JavaScript;

use FKSDB\Components\Controls\Loaders\Webloader;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class JavaScriptLoader extends Webloader {

    /**
     * @return mixed|string
     */
    protected function getTemplateFilePrefix() {
        return __DIR__ . DIRECTORY_SEPARATOR . 'JavaScript';
    }

}

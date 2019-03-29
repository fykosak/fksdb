<?php

namespace FKSDB\Components\Controls\Loaders\Stylesheet;

use FKSDB\Components\Controls\Loaders\Webloader;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class StylesheetLoader extends Webloader {

    /**
     * @return mixed|string
     */
    protected function getTemplateFilePrefix() {
        return __DIR__ . DIRECTORY_SEPARATOR . 'Stylesheet';
    }

}

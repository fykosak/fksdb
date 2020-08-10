<?php

namespace FKSDB\Components\Controls\Loaders\Stylesheet;

use FKSDB\Components\Controls\Loaders\WebLoader;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class StylesheetLoader extends WebLoader {

    protected function getTemplateFilePrefix(): string {
        return __DIR__ . DIRECTORY_SEPARATOR . 'Stylesheet';
    }
}

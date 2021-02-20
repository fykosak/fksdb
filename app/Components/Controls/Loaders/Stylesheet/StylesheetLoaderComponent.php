<?php

namespace FKSDB\Components\Controls\Loaders\Stylesheet;

use FKSDB\Components\Controls\Loaders\WebLoaderComponent;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class StylesheetLoaderComponent extends WebLoaderComponent {
    protected function getDir(): string {
        return __DIR__ . DIRECTORY_SEPARATOR;
    }
}

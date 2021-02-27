<?php

namespace FKSDB\Components\Controls\Loaders\JavaScript;

use FKSDB\Components\Controls\Loaders\WebLoaderComponent;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class JavaScriptLoaderComponent extends WebLoaderComponent {
    protected function getDir(): string {
        return __DIR__ . DIRECTORY_SEPARATOR;
    }
}

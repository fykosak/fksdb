<?php

namespace FKS\Components\Controls;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class StylesheetLoader extends Webloader {

    protected function getTemplateFilePrefix() {
        return __DIR__ . DIRECTORY_SEPARATOR . 'Stylesheet';
    }

}

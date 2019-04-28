<?php

namespace FKSDB\Components\DatabaseReflection;

use Nette\Templating\FileTemplate;

/**
 * Class StalkingRowComponent
 * @package FKSDB\Components\Controls\Stalking
 * @property FileTemplate $template
 */
class ListItemComponent extends AbstractRowComponent {
    /**
     * @return string
     */
    protected function getLayout(): string {
        return self::LAYOUT_LIST_GROUP;
    }
}

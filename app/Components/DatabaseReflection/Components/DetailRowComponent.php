<?php

namespace FKSDB\Components\DatabaseReflection;

use FKSDB\Components\Controls\Helpers\ValuePrinters\AbstractValue;
use Nette\Templating\FileTemplate;

/**
 * Class StalkingRowComponent
 * @package FKSDB\Components\Controls\Stalking
 * @property FileTemplate $template
 */
class DetailRowComponent extends AbstractRowComponent {

    /**
     * @return string
     */
    protected function getLayout(): string {
        return AbstractValue::LAYOUT_STALKING;
    }
}

<?php

namespace FKSDB\Components\DatabaseReflection;

/**
 * Class OnlyValueComponent
 * @package FKSDB\Components\DatabaseReflection
 */
class OnlyValueComponent extends AbstractRowComponent {
    /**
     * @return string
     */
    protected function getLayout(): string {
        return self::LAYOUT_ONLY_VALUE;
    }
}

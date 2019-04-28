<?php

namespace FKSDB\Components\DatabaseReflection\Org;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class PersonIdRowFactory
 * @package FKSDB\Components\DatabaseReflection\Org
 */
class PersonIdRowFactory extends AbstractOrgRowFactory {
    use DefaultPrinterTrait;

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Person id');
    }

    /**
     * @return string
     */
    protected function getModelAccessKey(): string {
        return 'person_id';
    }
}

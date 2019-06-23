<?php

namespace FKSDB\Components\DatabaseReflection\Org;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class OrgIdRow
 * @package FKSDB\Components\DatabaseReflection\Org
 */
class OrgIdRow extends AbstractOrgRowFactory {
    use DefaultPrinterTrait;

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Org Id');
    }

    /**
     * @return string
     */
    protected function getModelAccessKey(): string {
        return 'org_id';
    }
}

<?php

namespace FKSDB\Components\DatabaseReflection\Org;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class ContributionRow
 * @package FKSDB\Components\DatabaseReflection\Org
 */
class ContributionRow extends AbstractOrgRowFactory {
    use DefaultPrinterTrait;

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Contribution');
    }

    /**
     * @return string
     */
    public function getModelAccessKey(): string {
        return 'contribution';
    }
}

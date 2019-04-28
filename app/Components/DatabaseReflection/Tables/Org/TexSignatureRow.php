<?php

namespace FKSDB\Components\DatabaseReflection\Org;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class TexSignatureRow
 * @package FKSDB\Components\DatabaseReflection\Org
 */
class TexSignatureRow extends AbstractOrgRowFactory {
    use DefaultPrinterTrait;

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Tex signature');
    }

    /**
     * @return string
     */
    protected function getModelAccessKey(): string {
        return 'tex_signature';
    }
}

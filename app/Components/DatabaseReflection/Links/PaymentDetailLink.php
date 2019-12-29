<?php

namespace FKSDB\Components\DatabaseReflection\Links;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Utils\Html;

/**
 * Class PaymentDetailLink
 * @package FKSDB\Components\DatabaseReflection\Links
 */
class PaymentDetailLink extends AbstractRow {

    /**
     * @inheritDoc
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        // TODO: Implement createHtmlValue() method.
    }

    /**
     * @inheritDoc
     */
    public function getPermissionsValue(): int {
        // TODO: Implement getPermissionsValue() method.
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): string {
        return _('Detail');
    }
}

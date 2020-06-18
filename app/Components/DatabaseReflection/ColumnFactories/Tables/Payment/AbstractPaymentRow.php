<?php

namespace FKSDB\Components\DatabaseReflection\Payment;

use FKSDB\Components\DatabaseReflection\ColumnFactories\AbstractColumnFactory;
use Nette\Forms\Controls\BaseControl;
use FKSDB\Exceptions\NotImplementedException;

/**
 * Class AbstractPaymentRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class AbstractPaymentRow extends AbstractColumnFactory {
    /**
     * @param array $args
     * @return BaseControl
     * @throws NotImplementedException
     */
    public function createField(...$args): BaseControl {
        throw new NotImplementedException();
    }

    public function getPermissionsValue(): int {
        return self::PERMISSION_USE_GLOBAL_ACL;
    }
}

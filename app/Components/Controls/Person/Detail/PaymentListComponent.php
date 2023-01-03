<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Person\Detail;

use FKSDB\Models\ORM\FieldLevelPermissionValue;

class PaymentListComponent extends BaseComponent
{
    final public function render(): void
    {
        if ($this->beforeRender()) {
            $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'payment.latte');
        }
    }

    protected function getMinimalPermission(): FieldLevelPermissionValue
    {
        return FieldLevelPermissionValue::Restrict;
    }
}

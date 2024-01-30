<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Factories\Events;

use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Events\Model\Holder\Field;
use Nette\Forms\Controls\BaseControl;

interface FieldFactory
{
    public function createComponent(Field $field, BaseHolder $holder): BaseControl;

    public function setFieldDefaultValue(BaseControl $control, Field $field, BaseHolder $holder): void;
}

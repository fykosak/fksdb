<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Factories\Events;

use FKSDB\Models\Events\Model\Holder\Field;
use Nette\Forms\Controls\BaseControl;

interface FieldFactory
{

    public function createComponent(Field $field): BaseControl;

    public function setFieldDefaultValue(BaseControl $control, Field $field): void;
}

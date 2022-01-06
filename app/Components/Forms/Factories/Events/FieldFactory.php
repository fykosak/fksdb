<?php

declare(strict_types=1);

declare(strict_types=1);

namespace FKSDB\Components\Forms\Factories\Events;

use FKSDB\Models\Events\Model\Holder\DataValidator;
use FKSDB\Models\Events\Model\Holder\Field;
use Nette\Forms\Controls\BaseControl;

interface FieldFactory
{

    public function createComponent(Field $field): BaseControl;

    /**
     * Checks whether data are filled correctly (more than form validation as the validity
     * can depend on the machine state).
     */
    public function validate(Field $field, DataValidator $validator): void;

    public function setFieldDefaultValue(BaseControl $control, Field $field): void;
}

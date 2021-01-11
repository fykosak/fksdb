<?php

namespace FKSDB\Components\Forms\Factories\Events;

use FKSDB\Models\Events\Model\Holder\Field;

/**
 *
 * @author michal
 */
interface OptionsProvider {
    public function getOptions(Field $field): array;
}

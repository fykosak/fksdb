<?php

namespace FKSDB\Components\Forms\Factories\Events;

use FKSDB\Models\Events\Model\Holder\Field;

/**
 * @author Michal Koutný <michal@fykos.cz>
 */
interface OptionsProvider {
    public function getOptions(Field $field): array;
}

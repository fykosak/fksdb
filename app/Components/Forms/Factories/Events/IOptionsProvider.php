<?php

namespace FKSDB\Components\Forms\Factories\Events;

use Events\Model\Holder\Field;

/**
 *
 * @author michal
 */
interface IOptionsProvider {

    /**
     * @param Field $field
     * @return array  key => label
     */
    public function getOptions(Field $field);
}

?>

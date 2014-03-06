<?php

namespace FKSDB\Components\Forms\Factories\Events;

use Events\Model\Holder\Field;

/**
 *
 * @author michal
 */
interface IOptionsProvider {

    /**
     * @return array  key => label
     */
    public function getOptions(Field $field);
}

?>

<?php

namespace FKSDB\Components\Forms\Factories\Events;

use Events\Model\Holder\Field;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

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

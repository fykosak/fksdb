<?php

namespace FKSDB\Components\Forms\Factories\Events;

use Events\Model\Holder\Field;
use Nette\Object;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class ArrayOptions extends Object implements IOptionsProvider {

    private $options;

    function __construct($options, $useKeys = true) {
        if (!$useKeys) {
            $this->options = array_combine($options, $options);
        } else {
            $this->options = $options;
        }
    }

    public function getOptions(Field $field) {
        return $this->options;
    }

}

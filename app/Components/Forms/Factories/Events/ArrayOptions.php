<?php

namespace FKSDB\Components\Forms\Factories\Events;

use FKSDB\Events\Model\Holder\Field;
use Nette\SmartObject;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class ArrayOptions implements IOptionsProvider {

    use SmartObject;
    /**
     * @var mixed
     */
    private $options;

    /**
     * ArrayOptions constructor.
     * @param $options
     * @param bool $useKeys
     */
    function __construct($options, $useKeys = true) {
        if (!$useKeys) {
            $this->options = array_combine($options, $options);
        } else {
            $this->options = $options;
        }
    }

    /**
     * @param Field $field
     * @return array
     */
    public function getOptions(Field $field) {
        return $this->options;
    }

}

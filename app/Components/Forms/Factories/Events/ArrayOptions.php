<?php

namespace FKSDB\Components\Forms\Factories\Events;

use FKSDB\Models\Events\Model\Holder\Field;
use Nette\SmartObject;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class ArrayOptions implements IOptionsProvider {

    use SmartObject;

    private array $options;

    public function __construct(array $options, bool $useKeys = true) {
        if (!$useKeys) {
            $this->options = array_combine($options, $options);
        } else {
            $this->options = $options;
        }
    }

    public function getOptions(Field $field): array {
        return $this->options;
    }
}

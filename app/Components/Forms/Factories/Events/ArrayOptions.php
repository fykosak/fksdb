<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Factories\Events;

use FKSDB\Models\Events\Model\Holder\Field;
use Nette\SmartObject;

class ArrayOptions implements OptionsProvider {

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

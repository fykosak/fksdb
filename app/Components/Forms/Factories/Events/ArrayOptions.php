<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Factories\Events;

use Nette\SmartObject;

class ArrayOptions implements OptionsProvider
{
    use SmartObject;

    /** @phpstan-var array<string,string> */
    private array $options;

    /**
     * @phpstan-param array<string,string> $options
     */
    public function __construct(array $options, bool $useKeys = true)
    {
        if (!$useKeys) {
            $this->options = array_combine($options, $options);
        } else {
            $this->options = $options;
        }
    }

    /**
     * @phpstan-return array<string,string>
     */
    public function getOptions(): array
    {
        return $this->options;
    }
}

<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Types;

trait NumberFactoryTrait
{
    private string $nullValue;
    private ?string $prefix;
    private ?string $suffix;

    public function setNumberFactory(string $nullValue, ?string $prefix, ?string $suffix): void
    {
        $this->nullValue = $nullValue;
        $this->prefix = $prefix;
        $this->suffix = $suffix;
    }
}

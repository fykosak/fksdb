<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Types;

trait NumberFactoryTrait
{
    private string $nullValue;
    private ?string $prefix;
    private ?string $suffix;

    public function setNullValueFormat(string $nullValue): void
    {
        $this->nullValue = $nullValue;
    }

    public function setPrefix(string $prefix): void
    {
        $this->prefix = $prefix;
    }

    public function setSuffix(string $suffix): void
    {
        $this->suffix = $suffix;
    }
}

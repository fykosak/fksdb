<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Types;

use Nette\Utils\Html;

interface EnumColumn
{
    public function getBehaviorType(): string;

    public function badge(): Html;

    public function label(): string;
}

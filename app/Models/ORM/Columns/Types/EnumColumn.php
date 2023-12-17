<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Types;

use Fykosak\Utils\UI\Title;
use Nette\Utils\Html;

interface EnumColumn
{
    public function badge(): Html;

    public function label(): string;

    public function title(): Title;
}

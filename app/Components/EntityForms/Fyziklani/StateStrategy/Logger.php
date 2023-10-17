<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms\Fyziklani\StateStrategy;

class Logger
{
    public bool $memberAdded = false;
    public bool $memberRemoved = false;
    public bool $teacherAdded = false;
    public bool $teacherRemoved = false;
    public bool $isOrganizer = false;
    public bool $isNew;

    public function __construct(bool $isOrganizer, bool $isNew)
    {
        $this->isOrganizer = $isOrganizer;
        $this->isNew = $isNew;
    }
}

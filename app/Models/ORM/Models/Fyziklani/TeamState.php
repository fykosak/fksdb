<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Fyziklani;

// TODO to enum
use Nette\Utils\Html;

class TeamState
{
    public const APPLIED = 'applied';
    public const PENDING = 'pending';
    public const APPROVED = 'approved';
    public const SPARE = 'spare';
    public const PARTICIPATED = 'participated';
    public const MISSED = 'missed';
    public const DISQUALIFIED = 'disqualified';
    public const CANCELED = 'cancelled';

    public string $value;

    public function __construct(string $status)
    {
        $this->value = $status;
    }

    public static function tryFrom(?string $status): ?self
    {
        return $status ? new self($status) : null;
    }

    public function badge(): Html
    {
        // TODO
        return Html::el('span');
    }
}

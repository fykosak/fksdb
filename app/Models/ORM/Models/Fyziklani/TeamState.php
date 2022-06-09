<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Fyziklani;

// TODO to enum
use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use Nette\Utils\Html;

class TeamState implements EnumColumn
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

    public function label(): string
    {
        switch ($this->value) {
            case self::APPLIED:
                return _('applied');
            case self::PENDING:
                return _('pending');
            case self::APPROVED:
                return _('approved');
            case self::SPARE:
                return _('spare');
            case self::PARTICIPATED:
                return _('participated');
            case self::MISSED:
                return _('missed');
            case self::DISQUALIFIED:
                return _('disqualified');
            case self::CANCELED:
                return _('cancelled');
        }
    }

    public function cases(): array
    {
        //TODO
        return [];
    }
}

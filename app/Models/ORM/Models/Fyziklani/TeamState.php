<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Fyziklani;

// TODO to enum
use FKSDB\Models\Exceptions\NotImplementedException;
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
    public const CANCELLED = 'cancelled';

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
        $badge = '';
        switch ($this->value) {
            case self::APPLIED:
                $badge = 'badge bg-color-1';
                break;
            case self::PENDING:
                $badge = 'badge bg-color-2';
                break;
            case self::APPROVED:
                $badge = 'badge bg-color-7';
                break;
            case self::SPARE:
                $badge = 'badge bg-color-9';
                break;
            case self::PARTICIPATED:
                $badge = 'badge bg-color-3';
                break;
            case self::MISSED:
                $badge = 'badge bg-color-4';
                break;
            case self::DISQUALIFIED:
                $badge = 'badge bg-color-5';
                break;
            case self::CANCELLED:
                $badge = 'badge bg-color-6';
                break;
        }
        return Html::el('span')->addAttributes(['class' => $badge])->addText($this->label());
    }

    /**
     * @throws NotImplementedException
     */
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
            case self::CANCELLED:
                return _('canceled');
        }
        throw new NotImplementedException();
    }

    /**
     * @return TeamState[]
     */
    public static function cases(): array
    {
        return [
            new self(self::APPLIED),
            new self(self::PENDING),
            new self(self::APPROVED),
            new self(self::SPARE),
            new self(self::PARTICIPATED),
            new self(self::MISSED),
            new self(self::DISQUALIFIED),
            new self(self::CANCELLED),
        ];
    }
}

<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Fyziklani;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Utils\FakeStringEnum;
use Nette\Utils\Html;

final class TeamState extends FakeStringEnum implements EnumColumn
{
    public const APPLIED = 'applied';
    public const PENDING = 'pending';
    public const APPROVED = 'approved';
    public const SPARE = 'spare';
    public const PARTICIPATED = 'participated';
    public const MISSED = 'missed';
    public const DISQUALIFIED = 'disqualified';
    public const CANCELLED = 'cancelled';
    public const INIT = 'init'; // virtual state for correct ORM

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

    public function getBehaviorType(): string
    {
        switch ($this->value) {
            case self::APPLIED:
            case self::APPROVED:
                return 'info';
            case self::PENDING:
                return 'warning';
            case self::SPARE:
                return 'primary';
            case self::PARTICIPATED:
                return 'success';
            case self::MISSED:
            case self::CANCELLED:
            case self::INIT:
                return 'secondary';
            case self::DISQUALIFIED:
                return 'danger';
        }
        return '';
    }

    public function label(): string
    {
        switch ($this->value) {
            case self::APPLIED:
                return _('Applied');
            case self::PENDING:
                return _('Pending');
            case self::APPROVED:
                return _('Approved');
            case self::SPARE:
                return _('Spare');
            case self::PARTICIPATED:
                return _('Participated');
            case self::MISSED:
                return _('Missed');
            case self::DISQUALIFIED:
                return _('Disqualified');
            case self::CANCELLED:
                return _('Canceled');
        }
        return $this->value;
    }

    /**
     * @phpstan-return TeamState[]
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
            new self(self::INIT),
        ];
    }
}

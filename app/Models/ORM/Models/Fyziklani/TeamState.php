<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Fyziklani;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Utils\FakeStringEnum;
use Fykosak\Utils\UI\Title;
use Nette\Utils\Html;

final class TeamState extends FakeStringEnum implements EnumColumn
{
    // phpcs:disable
    public const Applied = 'applied';
    public const Arrived = 'arrived';
    public const Cancelled = 'cancelled';
    public const Disqualified = 'disqualified';
    public const Missed = 'missed';
    public const Participated = 'participated';
    public const Pending = 'pending';
    public const Spare = 'spare';

    public const Init = 'init'; // virtual state for correct ORM

    // phpcs:enable

    public function badge(): Html
    {
        return Html::el('span')
            ->addAttributes(['class' => 'badge bg-' . $this->behaviorType()])
            ->addText($this->label());
    }

    public function pseudoState(): self
    {
        switch ($this->value) {
            case self::Pending:
                return new self(self::Applied);
            default:
                return $this;
        }
    }

    public function behaviorType(): string
    {
        switch ($this->value) {
            case self::Arrived:
                return 'danger';
            case self::Applied:
                return 'info';
            case self::Pending:
                return 'warning';
            case self::Spare:
                return 'primary';
            case self::Participated:
                return 'success';
            case self::Missed:
            case self::Cancelled:
            case self::Init:
                return 'secondary';
            case self::Disqualified:
            default:
                return 'dark';
        }
    }

    public function label(): string
    {
        switch ($this->value) {
            case self::Arrived:
                return _('Arrived');
            case self::Applied:
                return _('Applied');
            case self::Pending:
                return _('Pending');
            case self::Spare:
                return _('Spare');
            case self::Participated:
                return _('Participated');
            case self::Missed:
                return _('Missed');
            case self::Disqualified:
                return _('Disqualified');
            case self::Cancelled:
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
            new self(self::Applied),
            new self(self::Arrived),
            new self(self::Pending),
            new self(self::Spare),
            new self(self::Participated),
            new self(self::Missed),
            new self(self::Disqualified),
            new self(self::Cancelled),
            new self(self::Init),
        ];
    }

    /**
     * @return self[]
     */
    public static function possiblyAttendingCases(): array
    {
        return [
            new self(self::Participated),
            new self(self::Spare),
            new self(self::Applied),
            new self(self::Arrived),
        ];
    }

    public function title(): Title
    {
        return new Title(null, $this->label());
    }
}

<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Fyziklani;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Utils\FakeStringEnum;
use Nette\Utils\Html;

final class TeamState extends FakeStringEnum implements EnumColumn
{
    // phpcs:disable
    public const Applied = 'applied';
    public const Approved = 'approved';
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
        $badge = '';
        switch ($this->value) {
            case self::Applied:
                $badge = 'badge bg-color-1';
                break;
            case self::Pending:
                $badge = 'badge bg-color-2';
                break;
            case self::Approved:
                $badge = 'badge bg-color-7';
                break;
            case self::Spare:
                $badge = 'badge bg-color-9';
                break;
            case self::Participated:
                $badge = 'badge bg-color-3';
                break;
            case self::Missed:
                $badge = 'badge bg-color-4';
                break;
            case self::Disqualified:
                $badge = 'badge bg-color-5';
                break;
            case self::Cancelled:
                $badge = 'badge bg-color-6';
                break;
            case self::Arrived:
                $badge = 'badge bg-color-8';
                break;
        }
        return Html::el('span')->addAttributes(['class' => $badge])->addText($this->label());
    }

    public function getPseudoState(): self
    {
        switch ($this->value) {
            case self::Pending:
            case self::Approved:
                return new self(self::Applied);
            case self::Participated:
                return new self(self::Participated);
            default:
                return clone $this;
        }
    }

    public function pseudoBadge(): Html
    {
        return $this->getPseudoState()->badge();
    }

    public function getBehaviorType(): string
    {
        switch ($this->value) {
            case self::Arrived:
            case self::Applied:
            case self::Approved:
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
                return 'danger';
        }
        return '';
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
            case self::Approved:
                return _('Approved');
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
            new self(self::Approved),
            new self(self::Spare),
            new self(self::Participated),
            new self(self::Missed),
            new self(self::Disqualified),
            new self(self::Cancelled),
            new self(self::Init),
        ];
    }
}

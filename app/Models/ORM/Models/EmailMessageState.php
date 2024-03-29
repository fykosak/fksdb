<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Utils\FakeStringEnum;
use Fykosak\Utils\UI\Title;
use Nette\Utils\Html;

final class EmailMessageState extends FakeStringEnum implements EnumColumn
{
    public const SAVED = 'saved'; // uložená, na ďalšiu úpravu
    public const WAITING = 'waiting'; //čaká na poslanie
    public const SENT = 'sent'; // úspešné poslané (môže sa napr. ešte odraziť)
    public const FAILED = 'failed'; // posielanie zlyhalo
    public const CANCELED = 'canceled'; // posielanie zrušené
    public const REJECTED = 'rejected'; // zastavené kvôli GDPR

    public function badge(): Html
    {
        switch ($this->value) {
            default:
            case self::CANCELED:
                $badge = 'secondary';
                break;
            case self::REJECTED:
            case self::FAILED:
                $badge = 'danger';
                break;
            case self::SAVED:
                $badge = 'info';
                break;
            case self::SENT:
                $badge = 'success';
                break;
            case self::WAITING:
                $badge = 'warning';
                break;
        }
        return Html::el('span')->addAttributes(['class' => 'badge bg-' . $badge])->addText($this->label());
    }

    public function label(): string
    {
        switch ($this->value) {
            default:
            case self::CANCELED:
                return _('Canceled');
            case self::FAILED:
                return _('Failed');
            case self::REJECTED:
                return _('Rejected');
            case self::SAVED:
                return _('Saved');
            case self::SENT:
                return _('Sent');
            case self::WAITING:
                return _('Waiting');
        }
    }

    public static function cases(): array
    {
        return [
            new self(self::SAVED),
            new self(self::WAITING),
            new self(self::SENT),
            new self(self::FAILED),
            new self(self::CANCELED),
            new self(self::REJECTED),
        ];
    }

    public function title(): Title
    {
        return new Title(null, $this->label());
    }
}

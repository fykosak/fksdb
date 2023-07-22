<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Utils\FakeStringEnum;
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
                $badge = 'badge bg-color-6';
                break;
            case self::FAILED:
                $badge = 'badge bg-color-4';
                break;
            case self::REJECTED:
                $badge = 'badge bg-color-7';
                break;
            case self::SAVED:
                $badge = 'badge bg-color-1';
                break;
            case self::SENT:
                $badge = 'badge bg-color-3';
                break;
            case self::WAITING:
                $badge = 'badge bg-color-2';
                break;
        }
        return Html::el('span')->addAttributes(['class' => $badge])->addText($this->label());
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
        return [];// TODO
    }
}

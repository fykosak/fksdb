<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Utils\FakeStringEnum;
use Fykosak\Utils\UI\Title;
use Nette\Utils\Html;

final class EmailMessageState extends FakeStringEnum implements EnumColumn
{
    //phpcs:disable
    public const Concept = 'concept'; // uložená, na ďalšiu úpravu
    public const Ready = 'ready'; // dokončený čaká na pridanie unsubscribed options
    public const Waiting = 'waiting'; //čaká na poslanie
    public const Sent = 'sent'; // úspešné poslané (môže sa napr. ešte odraziť)
    public const Failed = 'failed'; // posielanie zlyhalo
    public const Cancelled = 'canceled'; // posielanie zrušené
    public const Rejected = 'rejected'; // zastavené kvôli GDPR
    //phpcs:enable

    public function badge(): Html
    {
        return Html::el('span')
            ->addAttributes(['class' => 'badge bg-' . $this->behaviorType()])
            ->addText($this->label());
    }

    public function behaviorType(): string
    {
        switch ($this->value) {
            default:
            case self::Cancelled:
                return 'secondary';
            case self::Rejected:
            case self::Failed:
                return 'danger';
            case self::Concept:
                return 'info';
            case self::Sent:
                return 'success';
            case self::Ready:
            case self::Waiting:
                return 'warning';
        }
    }


    public function label(): string
    {
        switch ($this->value) {
            case self::Ready:
                return _('Ready');
            default:
            case self::Cancelled:
                return _('Canceled');
            case self::Failed:
                return _('Failed');
            case self::Rejected:
                return _('Rejected');
            case self::Concept:
                return _('Concept');
            case self::Sent:
                return _('Sent');
            case self::Waiting:
                return _('Waiting');
        }
    }

    public static function cases(): array
    {
        return [
            new self(self::Concept),
            new self(self::Ready),
            new self(self::Waiting),
            new self(self::Sent),
            new self(self::Failed),
            new self(self::Cancelled),
            new self(self::Rejected),
        ];
    }

    public function title(): Title
    {
        return new Title(null, $this->label());
    }
}

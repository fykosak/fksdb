<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use Fykosak\Utils\UI\Title;
use Nette\InvalidStateException;
use Nette\Utils\Html;

enum EmailMessageState: string implements EnumColumn
{
    case Concept = 'concept'; // uložená, na ďalšiu úpravu
    case Ready = 'ready'; // dokončený čaká na pridanie unsubscribed options
    case Waiting = 'waiting'; //čaká na poslanie
    case Sent = 'sent'; // úspešné poslané (môže sa napr. ešte odraziť)
    case Failed = 'failed'; // posielanie zlyhalo
    case Cancelled = 'canceled'; // posielanie zrušené
    case Rejected = 'rejected'; // zastavené kvôli GDPR

    public function badge(): Html
    {
        return Html::el('span')
            ->addAttributes(['class' => 'badge bg-' . $this->behaviorType()])
            ->addText($this->label());
    }

    public function behaviorType(): string
    {
        switch ($this) {
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
        throw new InvalidStateException();
    }


    public function label(): string
    {
        switch ($this) {
            case self::Ready:
                return _('Ready');
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
        throw new InvalidStateException();
    }

    public function title(): Title
    {
        return new Title(null, $this->label());
    }
}

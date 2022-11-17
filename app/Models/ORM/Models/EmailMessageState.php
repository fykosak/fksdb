<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Utils\FakeStringEnum;
use Nette\Utils\Html;

enum EmailMessageState: string implements EnumColumn
{
    case Saved = 'saved'; // uložená, na ďalšiu úpravu
    case Waiting = 'waiting'; //čaká na poslanie
    case Sent = 'sent'; // úspešné poslané (môže sa napr. ešte odraziť)
    case Failed = 'failed'; // posielanie zlyhalo
    case Canceled = 'canceled'; // posielanie zrušené
    case Rejected = 'rejected'; // zastavené kvôli GDPR

    public function getBehaviorType(): string
    {
        return match ($this) {
            self::Canceled => 'secondary',
            self::Failed => 'danger',
            self::Rejected => 'dark',
            self::Saved => 'info',
            self::Sent => 'success',
            self::Waiting => 'warning',
        };
    }

    public function badge(): Html
    {
        return Html::el('span')
            ->addAttributes(['class' => 'badge bg-' . $this->getBehaviorType()])
            ->addText($this->label());
    }

    public function label(): string
    {
        return match ($this) {
            self::Canceled => _('Canceled'),
            self::Failed => _('Failed'),
            self::Rejected => _('Rejected'),
            self::Saved => _('Saved'),
            self::Sent => _('Sent'),
            self::Waiting => _('Waiting'),
        };
    }
}

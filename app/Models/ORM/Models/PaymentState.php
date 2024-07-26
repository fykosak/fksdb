<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use Fykosak\Utils\UI\Title;
use Nette\Utils\Html;

enum PaymentState: string implements EnumColumn
{
    case Waiting = 'waiting'; // waiting for confirm payment
    case Received = 'received'; // payment received
    case Canceled = 'canceled'; // payment canceled
    case InProgress = 'in_progress'; // new payment
    case Init = 'init'; // virtual state for correct ORM

    public function badge(): Html
    {
        return Html::el('span')->addAttributes(['class' => 'badge bg-' . $this->behaviorType()])->addText(
            $this->label()
        );
    }

    public function behaviorType(): string
    {
        return match ($this) {
            self::InProgress => 'primary',
            self::Waiting => 'warning',
            self::Received => 'success',
            self::Canceled, self::Init => 'secondary',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::InProgress => _('New payment'),
            self::Waiting => _('Waiting for paying'),
            self::Received => _('Payment received'),
            self::Canceled => _('Payment canceled'),
            self::Init => _('Init'),
        };
    }

    public function title(): Title
    {
        return new Title(null, $this->label());
    }

    public function getBehaviorType(): string
    {
        return $this->behaviorType();
    }
}

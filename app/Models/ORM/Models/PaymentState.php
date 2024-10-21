<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use Fykosak\Utils\Localization\LocalizedString;
use Fykosak\Utils\UI\Title;
use Nette\Utils\Html;

enum PaymentState: string implements EnumColumn
{
    case Waiting = 'waiting'; // waiting for confirm payment
    case Received = 'received'; // payment received
    case Canceled = 'canceled'; // payment canceled
    case InProgress = 'in_progress';
    case Init = 'init'; // virtual state for correct ORM

    public function badge(): Html
    {
        return Html::el('span')->addAttributes(['class' => 'badge bg-' . $this->behaviorType()])->addText(
            $this->label()
        );
    }
    public function label(): string
    {
        switch ($this) {
            case self::InProgress:
                return _('In progress');
            case self::Waiting:
                return _('Waiting for paying');
            case self::Received:
                return _('Payment received');
            default:
            case self::Canceled:
                return _('Payment canceled');
        }
    }

    /**
     * @return LocalizedString<'cs'|'en'>
     */
    public function label2(): LocalizedString
    {
        switch ($this) {
            case self::InProgress:
                return new LocalizedString(['en' => 'In progress', 'cs' => 'V procesu']);
            case self::Waiting:
                return new LocalizedString(['en' => 'Waiting for paying', 'cs' => 'Čeká se na platbu']);
            case self::Received:
                return new LocalizedString(['en' => 'Payment received', 'cs' => 'Platba přijata']);
            default:
            case self::Canceled:
                return new LocalizedString(['en' => 'Payment canceled', 'cs' => 'Platba zrušena']);
        }
    }

    public function behaviorType(): string
    {
        switch ($this) {
            case self::InProgress:
                return 'primary';
            case self::Waiting:
                return 'warning';
            case self::Received:
                return 'success';
            default:
            case self::Canceled:
                return 'secondary';
        }
    }

    public function title(): Title
    {
        return new Title(null, $this->label());
    }
}

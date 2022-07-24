<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use Nette\Utils\Html;

class PaymentState implements EnumColumn
{
    public const WAITING = 'waiting'; // waiting for confirm payment
    public const RECEIVED = 'received'; // payment received
    public const CANCELED = 'canceled'; // payment canceled
    public const NEW = 'new'; // new payment

    public string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function badge(): Html
    {
        $badge = '';
        switch ($this->value) {
            case self::NEW:
                $badge = 'badge bg-primary';
                break;
            case self::WAITING:
                $badge = 'badge bg-warning';
                break;
            case self::RECEIVED:
                $badge = 'badge bg-success';
                break;
            case self::CANCELED:
                $badge = 'badge bg-secondary';
                break;
        }
        return Html::el('span')->addAttributes(['class' => $badge])->addText($this->label());
    }

    public function label(): string
    {
        switch ($this->value) {
            case self::NEW:
                return _('New payment');
            case self::WAITING:
                return _('Waiting for paying');
            case self::RECEIVED:
                return _('Payment received');
            default:
            case self::CANCELED:
                return _('Payment canceled');
        }
    }
}

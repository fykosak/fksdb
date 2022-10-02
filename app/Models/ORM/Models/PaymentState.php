<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Utils\FakeStringEnum;
use Nette\Utils\Html;

class PaymentState extends FakeStringEnum implements EnumColumn
{
    public const WAITING = 'waiting'; // waiting for confirm payment
    public const RECEIVED = 'received'; // payment received
    public const CANCELED = 'canceled'; // payment canceled
    public const NEW = 'new'; // new payment
    public const INIT = 'init'; // virtual state for correct ORM

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

    public static function cases(): array
    {
        return [];
    }
}

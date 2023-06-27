<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Machine;

use FKSDB\Models\ORM\Models\PaymentModel;
use FKSDB\Models\ORM\Services\PaymentService;
use FKSDB\Models\Transitions\Holder\PaymentHolder;
use Fykosak\NetteORM\Model;
use Nette\Database\Explorer;

final class PaymentMachine extends Machine
{
    private PaymentService $paymentService;

    public function __construct(Explorer $explorer, PaymentService $paymentService)
    {
        parent::__construct($explorer);
        $this->paymentService = $paymentService;
    }

    /**
     * @param PaymentModel|null $model
     */
    public function createHolder(Model $model): PaymentHolder
    {
        return new PaymentHolder($model, $this->paymentService);
    }
}

<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Machine;

use FKSDB\Models\ORM\Models\EmailMessageModel;
use FKSDB\Models\ORM\Services\EmailMessageService;
use FKSDB\Models\Transitions\Holder\EmailHolder;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use Fykosak\NetteORM\Model\Model;

/**
 * @phpstan-extends Machine<EmailHolder>
 */
final class EmailMachine extends Machine
{
    private EmailMessageService $service;

    public function __construct(EmailMessageService $service)
    {
        $this->service = $service;
    }

    /**
     * @param EmailMessageModel $model
     */
    public function createHolder(Model $model): ModelHolder
    {
        return new EmailHolder($model, $this->service);
    }
}

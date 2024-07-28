<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Machine;

use FKSDB\Models\ORM\Models\EmailMessageModel;
use FKSDB\Models\ORM\Services\EmailMessageService;
use FKSDB\Models\Transitions\Holder\EmailHolder;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use Fykosak\NetteORM\Model\Model;
use Nette\Database\Explorer;

/**
 * @phpstan-extends Machine<EmailHolder>
 */
final class EmailMachine extends Machine
{
    private EmailMessageService $service;

    public function __construct(
        Explorer $explorer,
        EmailMessageService $service
    ) {
        parent::__construct($explorer);
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

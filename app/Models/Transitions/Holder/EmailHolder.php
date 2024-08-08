<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Holder;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\ORM\Models\EmailMessageModel;
use FKSDB\Models\ORM\Models\EmailMessageState;
use FKSDB\Models\ORM\Services\EmailMessageService;

/**
 * @phpstan-implements ModelHolder<EmailMessageModel,EmailMessageState>
 */
final class EmailHolder implements ModelHolder
{
    private EmailMessageService $service;
    private EmailMessageModel $model;

    public function __construct(EmailMessageModel $model, EmailMessageService $service)
    {
        $this->service = $service;
        $this->model = $model;
    }

    public function getModel(): EmailMessageModel
    {
        return $this->model;
    }

    /**
     * @phpstan-param EmailMessageState $newState
     */
    public function setState(EnumColumn $newState): void
    {
        $this->service->storeModel(['state' => $newState->value], $this->model);
    }

    public function getState(): EmailMessageState
    {
        return $this->model->state;
    }
}

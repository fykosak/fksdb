<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\Models\EmailMessageModel;
use FKSDB\Models\ORM\Models\EmailMessageState;
use Fykosak\NetteORM\Service;
use Fykosak\NetteORM\TypedSelection;

/**
 * @phpstan-extends Service<EmailMessageModel>
 */
final class EmailMessageService extends Service
{
    /**
     * @phpstan-return TypedSelection<EmailMessageModel>
     */
    public function getMessagesToSend(int $limit): TypedSelection
    {
        return $this->getTable()->where('state', EmailMessageState::WAITING)->limit($limit);
    }

    /**
     * @phpstan-param array{
     *     recipient_person_id?:int,
     *     recipient?:string,
     *     sender:string,
     *     reply_to?:string|null,
     *     subject:string,
     *     carbon_copy?:string,
     *     blind_carbon_copy?:string,
     *     text:string,
     * } $data
     */
    public function addMessageToSend(array $data): EmailMessageModel
    {
        $data['state'] = EmailMessageState::WAITING;
        if (!isset($data['reply_to'])) {
            $data['reply_to'] = $data['sender'];
        }
        return $this->storeModel($data);
    }
}

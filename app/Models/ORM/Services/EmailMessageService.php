<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\Models\EmailMessageState;
use Fykosak\NetteORM\Exceptions\ModelException;
use FKSDB\Models\ORM\Models\EmailMessageModel;
use Fykosak\NetteORM\TypedSelection;
use Nette\Database\Table\ActiveRow;
use Fykosak\NetteORM\Service;

/**
 * @method EmailMessageModel createNewModel(array $data)
 */
class EmailMessageService extends Service
{

    public function getMessagesToSend(int $limit): TypedSelection
    {
        return $this->getTable()->where('state', EmailMessageState::WAITING)->limit($limit);
    }

    public function addMessageToSend(array $data): EmailMessageModel
    {
        $data['state'] = EmailMessageState::WAITING;
        if (!isset($data['reply_to'])) {
            $data['reply_to'] = $data['sender'];
        }
        return $this->createNewModel($data);
    }
}

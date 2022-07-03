<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services;

use Fykosak\NetteORM\Exceptions\ModelException;
use FKSDB\Models\ORM\Models\ModelEmailMessage;
use Fykosak\NetteORM\TypedSelection;
use Nette\Database\Table\ActiveRow;
use Fykosak\NetteORM\Service;

/**
 * @method ModelEmailMessage createNewModel(array $data)
 */
class ServiceEmailMessage extends Service
{

    public function getMessagesToSend(int $limit): TypedSelection
    {
        return $this->getTable()->where('state', ModelEmailMessage::STATE_WAITING)->limit($limit);
    }

    /**
     * @return ModelEmailMessage|ActiveRow
     * @throws ModelException
     */
    public function addMessageToSend(array $data): ModelEmailMessage
    {
        $data['state'] = ModelEmailMessage::STATE_WAITING;
        if (!isset($data['reply_to'])) {
            $data['reply_to'] = $data['sender'];
        }
        return $this->createNewModel($data);
    }
}

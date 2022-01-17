<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services;

use Fykosak\NetteORM\Exceptions\ModelException;
use FKSDB\Models\ORM\Models\ModelEmailMessage;
use Fykosak\NetteORM\TypedTableSelection;
use Nette\Database\Table\ActiveRow;
use Fykosak\NetteORM\AbstractService;

/**
 * @method ModelEmailMessage createNewModel(array $data)
 */
class ServiceEmailMessage extends AbstractService
{

    public function getMessagesToSend(int $limit): TypedTableSelection
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

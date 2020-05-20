<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelEmailMessage;
use FKSDB\ORM\Tables\TypedTableSelection;
use Nette\Database\Table\ActiveRow;

/**
 * Class ServiceEmailMessage
 * @package FKSDB\ORM\Services
 */
class ServiceEmailMessage extends AbstractServiceSingle {

    public function getMessagesToSend(int $limit): TypedTableSelection {
        return $this->getTable()->where('state', ModelEmailMessage::STATE_WAITING)->limit($limit);
    }

    public function getModelClassName(): string {
        return ModelEmailMessage::class;
    }

    protected function getTableName(): string {
        return DbNames::TAB_EMAIL_MESSAGE;
    }

    /**
     * @param array $data
     * @param int $priority
     * @return ModelEmailMessage|ActiveRow
     */
    public function addMessageToSend(array $data, int $priority = 0): ModelEmailMessage {
        $data['state'] = ModelEmailMessage::STATE_WAITING;
        if (!isset($data['reply_to'])) {
            $data['reply_to'] = $data['sender'];
        }
        return $this->createNewModel($data);
    }
}

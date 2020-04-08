<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelEmailMessage;
use FKSDB\ORM\Tables\TypedTableSelection;
use Nette\Database\Table\Selection;

/**
 * Class ServiceEmailMessage
 * @package FKSDB\ORM\Services
 */
class ServiceEmailMessage extends AbstractServiceSingle {
    /**
     * @param int $limit
     * @return TypedTableSelection
     */
    public function getMessagesToSend(int $limit): Selection {
        return $this->getTable()->where('state', ModelEmailMessage::STATE_WAITING)->limit($limit);
    }

    /**
     * @inheritDoc
     */
    public function getModelClassName(): string {
        return ModelEmailMessage::class;
    }

    /**
     * @inheritDoc
     */
    protected function getTableName(): string {
        return DbNames::TAB_EMAIL_MESSAGE;
    }

    /**
     * @param array $data
     * @param int $priority
     * @return AbstractModelSingle
     */
    public function addMessageToSend(array $data, int $priority = 0): AbstractModelSingle {
        $data['state'] = ModelEmailMessage::STATE_WAITING;
        if (!isset($data['reply_to'])) {
            $data['reply_to'] = $data['sender'];
        }
        return $this->createNewModel($data);
    }
}

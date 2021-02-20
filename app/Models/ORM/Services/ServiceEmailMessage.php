<?php

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\Exceptions\ModelException;
use FKSDB\Models\ORM\Models\ModelEmailMessage;
use FKSDB\Models\ORM\Tables\TypedTableSelection;
use Nette\Database\Table\ActiveRow;

/**
 * Class ServiceEmailMessage
 * @author Michal Červeňák <miso@fykos.cz>
 * @method ModelEmailMessage createNewModel(array $data)
 */
class ServiceEmailMessage extends AbstractServiceSingle {

    public function getMessagesToSend(int $limit): TypedTableSelection {
        return $this->getTable()->where('state', ModelEmailMessage::STATE_WAITING)->limit($limit);
    }

    /**
     * @param array $data
     * @return ModelEmailMessage|ActiveRow
     * @throws ModelException
     */
    public function addMessageToSend(array $data): ModelEmailMessage {
        $data['state'] = ModelEmailMessage::STATE_WAITING;
        if (!isset($data['reply_to'])) {
            $data['reply_to'] = $data['sender'];
        }
        return $this->createNewModel($data);
    }
}

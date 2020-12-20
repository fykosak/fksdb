<?php

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\DeprecatedLazyDBTrait;
use FKSDB\Models\ORM\Models\ModelEmailMessage;
use FKSDB\Models\ORM\Tables\TypedTableSelection;
use Nette\Database\Table\ActiveRow;

/**
 * Class ServiceEmailMessage
 * @author Michal Červeňák <miso@fykos.cz>
 * @method ModelEmailMessage createNewModel(array $data)
 */
class ServiceEmailMessage extends AbstractServiceSingle {

    use DeprecatedLazyDBTrait;

    public function getMessagesToSend(int $limit): TypedTableSelection {
        return $this->getTable()->where('state', ModelEmailMessage::STATE_WAITING)->limit($limit);
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

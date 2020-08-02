<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\DeprecatedLazyDBTrait;
use FKSDB\ORM\Models\ModelEmailMessage;
use FKSDB\ORM\Tables\TypedTableSelection;
use Nette\Database\Context;
use Nette\Database\IConventions;
use Nette\Database\Table\ActiveRow;

/**
 * Class ServiceEmailMessage
 * @author Michal Červeňák <miso@fykos.cz>
 * @method ModelEmailMessage createNewModel(array $data)
 */
class ServiceEmailMessage extends AbstractServiceSingle {
    use DeprecatedLazyDBTrait;

    /**
     * ServiceEmailMessage constructor.
     * @param Context $connection
     * @param IConventions $conventions
     */
    public function __construct(Context $connection, IConventions $conventions) {
        parent::__construct($connection, $conventions, DbNames::TAB_EMAIL_MESSAGE, ModelEmailMessage::class);
    }

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

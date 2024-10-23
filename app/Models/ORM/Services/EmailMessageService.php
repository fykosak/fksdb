<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\Models\EmailMessageModel;
use FKSDB\Models\ORM\Models\EmailMessageState;
use FKSDB\Models\ORM\Models\EmailMessageTopic;
use FKSDB\Modules\Core\Language;
use Fykosak\NetteORM\Selection\TypedSelection;
use Fykosak\NetteORM\Service\Service;

/**
 * @phpstan-extends Service<EmailMessageModel>
 * @phpstan-type TMessageData = array{
 *     recipient_person_id?:int,
 *     recipient?:string,
 *     sender:string,
 *     reply_to?:string,
 *     subject:string,
 *     carbon_copy?:string,
 *     blind_carbon_copy?:string,
 *     inner_text:string,
 *     topic:EmailMessageTopic,
 *     lang: Language,
 *     priority?:int|bool
 * }
 */
final class EmailMessageService extends Service
{
    /**
     * @phpstan-return TypedSelection<EmailMessageModel>
     */
    public function getMessagesToSend(int $limit): TypedSelection
    {
        return $this->getTable()
            ->where('state', EmailMessageState::Waiting->value)
            ->order('priority DESC')
            ->limit($limit);//@phpstan-ignore-line
    }

    /**
     * @phpstan-param TMessageData $data
     */
    public function addMessageToSend(array $data): EmailMessageModel
    {
        $data['state'] = EmailMessageState::Ready->value;
        if (!isset($data['reply_to'])) {
            $data['reply_to'] = $data['sender'];
        }
        if (!isset($data['priority'])) {
            $data['priority'] = 1;
        }
        $data['topic'] = $data['topic']->value;
        $data['lang'] = $data['lang']->value;
        return $this->storeModel($data);
    }
}

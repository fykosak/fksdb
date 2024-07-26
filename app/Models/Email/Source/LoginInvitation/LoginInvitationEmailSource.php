<?php

declare(strict_types=1);

namespace FKSDB\Models\Email\Source\LoginInvitation;

use FKSDB\Models\Email\EmailSource;
use FKSDB\Models\ORM\Models\AuthTokenModel;
use FKSDB\Models\ORM\Models\EmailMessageTopic;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Modules\Core\Language;

/**
 * @phpstan-extends EmailSource<array{
 *     token:AuthTokenModel,
 * },array{
 *      person:PersonModel,
 *      token:AuthTokenModel,
 *      lang:Language,
 *  }>
 */
class LoginInvitationEmailSource extends EmailSource
{
    protected function getSource(array $params): array
    {
        $lang = $params['lang'];
        $person = $params['person'];
        $token = $params['token'];
        return [
            [
                'template' => [
                    'file' => __DIR__ . "/layout.$lang->value.latte",
                    'data' => [
                        'token' => $token,
                    ],
                ],
                'data' => [
                    'sender' => 'FKSDB <fksdb@fykos.cz>',
                    'recipient_person_id' => $person->person_id,
                    'topic' => EmailMessageTopic::from(EmailMessageTopic::Internal),
                    'lang' => $lang,
                ]
            ]
        ];
    }
}

<?php

declare(strict_types=1);

namespace FKSDB\Models\Email\Source\PasswordRecovery;

use FKSDB\Models\Email\EmailSource;
use FKSDB\Models\ORM\Models\AuthTokenModel;
use FKSDB\Models\ORM\Models\EmailMessageTopic;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Modules\Core\Language;

/**
 * @phpstan-extends EmailSource<array{
 *     person:PersonModel,
 *     token:AuthTokenModel,
 * },array{
 *      person:PersonModel,
 *      lang:Language,
 *      token:AuthTokenModel,
 *  }>
 */
final class PasswordRecoveryEmail extends EmailSource
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
                        'person' => $person,
                    ],
                ],
                'data' => [
                    'sender' => 'FKSDB <fksdb@fykos.cz>',
                    'recipient_person_id' => $person->person_id,
                    'topic' => EmailMessageTopic::Internal,
                    'lang' => $lang,
                ]
            ]
        ];
    }
}

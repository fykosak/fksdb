<?php

declare(strict_types=1);

namespace FKSDB\Models\Email\Source\LoginInvitation;

use FKSDB\Models\Email\EmailSource;
use FKSDB\Models\ORM\Models\AuthTokenModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Modules\Core\Language;

/**
 * @phpstan-extends EmailSource<array{
 *     token:AuthTokenModel,
 *     lang:Language,
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
                    'file' => __DIR__ . '/loginInvitation.latte',
                    'data' => [
                        'token' => $token,
                        'lang' => $lang,
                    ],
                ],
                'lang' => $lang,
                'data' => [
                    'sender' => 'FKSDB <fksdb@fykos.cz>',
                    'recipient_person_id' => $person->person_id,
                ]
            ]
        ];
    }
}

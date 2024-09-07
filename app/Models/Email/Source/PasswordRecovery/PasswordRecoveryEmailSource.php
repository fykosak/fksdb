<?php

declare(strict_types=1);

namespace FKSDB\Models\Email\Source\PasswordRecovery;

use FKSDB\Models\Email\EmailSource;

/**
 * @phpstan-extends EmailSource<array{
 *     person:PersonModel,
 *     token:AuthTokenModel,
 *     lang:Language,
 * },array{
 *      person:PersonModel,
 *      lang:Language,
 *      token:AuthTokenModel,
 *  }>
 */
class PasswordRecoveryEmailSource extends EmailSource
{
    protected function getSource(array $params): array
    {
        $lang = $params['lang'];
        $person = $params['person'];
        $token = $params['token'];

        return [
            [
                'template' => [
                    'file' => __DIR__ . '/recovery.latte',
                    'data' => [
                        'token' => $token,
                        'person' => $person,
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

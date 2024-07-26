<?php

declare(strict_types=1);

namespace FKSDB\Models\Email\Source\PasswordRecovery;

use FKSDB\Models\Email\Source\EmailSource;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\AuthTokenModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Modules\Core\Language;
use Fykosak\Utils\Localization\LocalizedString;
use Fykosak\Utils\UI\Title;

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

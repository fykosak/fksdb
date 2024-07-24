<?php

declare(strict_types=1);

namespace FKSDB\Models\Email\Source\LoginInvitation;

use FKSDB\Models\Email\Source\MailSource;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\AuthTokenModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Modules\Core\Language;
use Fykosak\Utils\Localization\LocalizedString;
use Fykosak\Utils\UI\Title;

/**
 * @phpstan-extends MailSource<array{
 *     token:AuthTokenModel,
 *     lang:Language,
 * },array{
 *      person:PersonModel,
 *      token:AuthTokenModel,
 *      lang:Language,
 *  }>
 */
class LoginInvitationEmailSource extends MailSource
{
    /**
     * @throws NotImplementedException
     */
    public function getExpectedParams(): array
    {
        throw new NotImplementedException();
    }

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

    /**
     * @throws NotImplementedException
     */
    public function title(): Title
    {
        throw new NotImplementedException();
    }

    /**
     * @throws NotImplementedException
     */
    public function description(): LocalizedString //@phpstan-ignore-line
    {
        throw new NotImplementedException();
    }
}

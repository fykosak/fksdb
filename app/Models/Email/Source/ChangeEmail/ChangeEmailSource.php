<?php

declare(strict_types=1);

namespace FKSDB\Models\Email\Source\ChangeEmail;

use FKSDB\Models\Authentication\Exceptions\ChangeInProgressException;
use FKSDB\Models\Email\Source\EmailSource;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\AuthTokenModel;
use FKSDB\Models\ORM\Models\AuthTokenType;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\AuthTokenService;
use FKSDB\Modules\Core\Language;
use Fykosak\Utils\Localization\LocalizedString;
use Fykosak\Utils\UI\Title;

/**
 * @phpstan-extends EmailSource<array{
 *     person:PersonModel,
 *     newEmail:string,
 *     token?:AuthTokenModel,
 *     lang:Language,
 * },array{
 *      person:PersonModel,
 *      newEmail:string,
 *      lang:Language,
 *  }>
 */
class ChangeEmailSource extends EmailSource
{
    private AuthTokenService $tokenService;

    public function injectTokenService(AuthTokenService $tokenService): void
    {
        $this->tokenService = $tokenService;
    }

    /**
     * @throws NotImplementedException
     */
    public function getExpectedParams(): array
    {
        throw new NotImplementedException();
    }

    /**
     * @throws ChangeInProgressException
     */
    protected function getSource(array $params): array
    {
        $lang = $params['lang'];
        $person = $params['person'];
        $newEmail = $params['newEmail'];

        $token = $this->tokenService->createToken(
            $person->getLogin(),
            AuthTokenType::from(AuthTokenType::CHANGE_EMAIL),
            (new \DateTime())->modify('+20 minutes'),
            $newEmail
        );
        $oldData = [
            'template' => [
                'file' => __DIR__ . '/email.old.latte',
                'data' => ['lang' => $lang, 'person' => $person, 'newEmail' => $newEmail,],
            ],
            'lang' => $lang,
            'data' => [
                'sender' => 'FKSDB <fksdb@fykos.cz>',
                'recipient' => (string)$person->getInfo()->email,
            ]
        ];
        $newData = [
            'template' => [
                'file' => __DIR__ . '/email.new.latte',
                'data' => ['lang' => $lang, 'person' => $person, 'newEmail' => $newEmail, 'token' => $token,],
            ],
            'lang' => $lang,
            'data' => [
                'sender' => 'FKSDB <fksdb@fykos.cz>',
                'recipient' => $newEmail,
            ]
        ];
        return [$oldData, $newData];
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

<?php

declare(strict_types=1);

namespace FKSDB\Models\Authentication;

use FKSDB\Models\Email\Source\LoginInvitation\LoginInvitationEmail;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\AuthTokenType;
use FKSDB\Models\ORM\Models\LoginModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\AuthTokenService;
use FKSDB\Models\ORM\Services\LoginService;
use FKSDB\Modules\Core\Language;
use Fykosak\Utils\Localization\GettextTranslator;
use Nette\DI\Container;
use Nette\SmartObject;
use Nette\Utils\DateTime;

class AccountManager
{
    use SmartObject;

    private string $invitationExpiration;
    private LoginService $loginService;
    private AuthTokenService $authTokenService;
    private Container $container;

    public function __construct(
        string $invitationExpiration,
        Container $container,
        LoginService $loginService,
        AuthTokenService $authTokenService
    ) {
        $this->invitationExpiration = $invitationExpiration;
        $this->loginService = $loginService;
        $this->authTokenService = $authTokenService;
        $this->container = $container;
    }

    /**
     * Creates login and invites user to set up the account.
     * @throws BadTypeException
     * @throws \Exception
     * @throws \Throwable
     * @phpstan-param GettextTranslator<'cs'|'en'> $translator
     */
    public function sendLoginWithInvitation(PersonModel $person, GettextTranslator $translator): LoginModel
    {
        $login = $this->loginService->createLogin($person);

        $until = DateTime::from($this->invitationExpiration);
        $token = $this->authTokenService->createToken(
            $login,
            AuthTokenType::from(AuthTokenType::InitialLogin),
            new DateTime(),
            $until
        );
        $email = new LoginInvitationEmail($this->container);
        $email->createAndSend([
            'token' => $token,
            'person' => $person,
            'lang' => Language::from($person->getPreferredLang() ?? $translator->lang),
        ]);
        return $login;
    }
}

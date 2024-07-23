<?php

declare(strict_types=1);

namespace FKSDB\Models\Authentication;

use FKSDB\Models\Authentication\Exceptions\RecoveryExistsException;
use FKSDB\Models\Authentication\Exceptions\RecoveryNotImplementedException;
use FKSDB\Models\Email\Source\LoginInvitation\LoginInvitationEmailSource;
use FKSDB\Models\Email\Source\PasswordRecovery\PasswordRecoveryEmailSource;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\AuthTokenModel;
use FKSDB\Models\ORM\Models\AuthTokenType;
use FKSDB\Models\ORM\Models\LoginModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\AuthTokenService;
use FKSDB\Models\ORM\Services\LoginService;
use FKSDB\Modules\Core\Language;
use Nette\Application\BadRequestException;
use Nette\DI\Container;
use Nette\SmartObject;
use Nette\Utils\DateTime;

class AccountManager
{
    use SmartObject;

    private string $invitationExpiration;
    private string $recoveryExpiration;
    private LoginService $loginService;
    private AuthTokenService $authTokenService;
    private Container $container;

    public function __construct(
        string $invitationExpiration,
        string $recoveryExpiration,
        Container $container,
        LoginService $loginService,
        AuthTokenService $authTokenService
    ) {
        $this->invitationExpiration = $invitationExpiration;
        $this->recoveryExpiration = $recoveryExpiration;
        $this->loginService = $loginService;
        $this->authTokenService = $authTokenService;
        $this->container = $container;
    }

    /**
     * Creates login and invites user to set up the account.
     * @throws BadTypeException
     * @throws \Exception
     */
    public function sendLoginWithInvitation(PersonModel $person, Language $lang): LoginModel
    {
        $login = $this->loginService->createLogin($person);

        $until = DateTime::from($this->invitationExpiration);
        $token = $this->authTokenService->createToken(
            $login,
            AuthTokenType::from(AuthTokenType::INITIAL_LOGIN),
            $until
        );
        $email = new LoginInvitationEmailSource($this->container);
        $email->createAndSend([
            'token' => $token,
            'person' => $person,
            'lang' => Language::from($person->getPreferredLang() ?? $lang->value),
        ]);
        return $login;
    }

    /**
     * @throws BadTypeException
     * @throws \Exception
     */
    public function sendRecovery(LoginModel $login, Language $lang): void
    {
        if (!$login->person_id) {
            throw new RecoveryNotImplementedException();
        }
        /** @var AuthTokenModel|null $token */
        $token = $login->getActiveTokens(AuthTokenType::from(AuthTokenType::RECOVERY))->fetch();
        if ($token) {
            throw new RecoveryExistsException();
        }

        $until = DateTime::from($this->recoveryExpiration);
        $token = $this->authTokenService->createToken($login, AuthTokenType::from(AuthTokenType::RECOVERY), $until);

        $person = $login->person;
        if (!$person) {
            throw new BadRequestException();
        }
        $source = new PasswordRecoveryEmailSource($this->container);
        $source->createAndSend([
            'token' => $token,
            'person' => $person,
            'lang' => $lang,
        ]);
    }
}

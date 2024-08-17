<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Recovery;

use FKSDB\Components\Controls\FormComponent\FormComponent;
use FKSDB\Models\Authentication\Exceptions\RecoveryException;
use FKSDB\Models\Authentication\Exceptions\RecoveryExistsException;
use FKSDB\Models\Authentication\PasswordAuthenticator;
use FKSDB\Models\Email\Source\PasswordRecovery\PasswordRecoveryEmail;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\AuthTokenType;
use FKSDB\Models\ORM\Services\AuthTokenService;
use FKSDB\Models\Utils\Utils;
use FKSDB\Modules\Core\Language;
use Fykosak\Utils\Logging\Message;
use Nette\Application\BadRequestException;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;
use Nette\Security\AuthenticationException;
use Nette\Utils\DateTime;

class RecoveryForm extends FormComponent
{
    private AuthTokenService $authTokenService;
    private PasswordAuthenticator $passwordAuthenticator;

    final public function inject(
        AuthTokenService $authTokenService,
        PasswordAuthenticator $passwordAuthenticator
    ): void {
        $this->authTokenService = $authTokenService;
        $this->passwordAuthenticator = $passwordAuthenticator;
    }

    protected function getTemplatePath(): string
    {
        return __DIR__ . '/layout.latte';
    }

    /**+
     * @throws BadRequestException
     * @throws BadTypeException
     * @throws \Throwable
     */
    protected function handleSuccess(Form $form): void
    {
        $connection = $this->authTokenService->explorer->getConnection();
        try {
            /**
             * @phpstan-var array{id:string} $values
             */
            $values = $form->getValues('array');
            $connection->beginTransaction();
            $login = $this->passwordAuthenticator->findLogin($values['id']);
            if ($login->hasActiveToken(AuthTokenType::from(AuthTokenType::Recovery))) {
                throw new RecoveryExistsException();
            }

            $until = DateTime::from($this->getContext()->getParameters()['recovery']['expiration']);
            $token = $this->authTokenService->createToken(
                $login,
                AuthTokenType::from(AuthTokenType::Recovery),
                new DateTime(),
                $until
            );

            $person = $login->person;
            if (!$person) {
                throw new BadRequestException();
            }
            $source = new PasswordRecoveryEmail($this->getContext());
            $source->createAndSend([
                'token' => $token,
                'person' => $person,
                'lang' => Language::from($login->person->getPreferredLang() ?? $this->translator->lang),
            ]);

            $email = Utils::cryptEmail($login->person->getInfo()->email);
            $this->getPresenter()->flashMessage(
                sprintf(_('Further instructions for the recovery have been sent to %s.'), $email),
                Message::LVL_SUCCESS
            );
            $connection->commit();
            $this->getPresenter()->redirect('login');
        } catch (AuthenticationException | RecoveryException $exception) {
            $this->getPresenter()->flashMessage($exception->getMessage(), Message::LVL_ERROR);
            $connection->rollBack();
        }
    }

    protected function appendSubmitButton(Form $form): SubmitButton
    {
        return $form->addSubmit('send', _('Continue'));
    }

    protected function configureForm(Form $form): void
    {
        $form->addText('id', _('Login or e-mail address'))
            ->addRule(Form::FILLED, _('Insert login or email address.'));
        $form->addProtection(_('The form has expired. Please send it again.'));
    }
}

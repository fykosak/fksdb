<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Person\Edit;

use FKSDB\Components\EntityForms\EntityFormComponent;
use FKSDB\Components\Forms\Rules\UniqueEmail;
use FKSDB\Models\Authentication\Exceptions\ChangeInProgressException;
use FKSDB\Models\Email\TemplateFactory;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Columns\OmittedControlException;
use FKSDB\Models\ORM\Models\AuthTokenType;
use FKSDB\Models\ORM\Models\EmailMessageTopic;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\ReflectionFactory;
use FKSDB\Models\ORM\Services\AuthTokenService;
use FKSDB\Models\ORM\Services\EmailMessageService;
use FKSDB\Models\ORM\Services\LoginService;
use FKSDB\Modules\Core\Language;
use Fykosak\Utils\Logging\Message;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;
use Tracy\Debugger;

/**
 * @phpstan-extends EntityFormComponent<PersonModel>
 */
class ChangeEmailComponent extends EntityFormComponent
{
    private ReflectionFactory $reflectionFormFactory;
    private TemplateFactory $templateFactory;
    private LoginService $loginService;
    private AuthTokenService $authTokenService;
    private EmailMessageService $emailMessageService;

    public function inject(
        ReflectionFactory $reflectionFormFactory,
        TemplateFactory $templateFactory,
        LoginService $loginService,
        AuthTokenService $authTokenService,
        EmailMessageService $emailMessageService
    ): void {
        $this->reflectionFormFactory = $reflectionFormFactory;
        $this->templateFactory = $templateFactory;
        $this->loginService = $loginService;
        $this->authTokenService = $authTokenService;
        $this->emailMessageService = $emailMessageService;
    }

    public function render(): void
    {
        $login = $this->model->getLogin();
        $this->template->lang = Language::tryFrom($this->translator->lang);
        $this->template->changeActive = $login &&
            $login->getActiveTokens(AuthTokenType::from(AuthTokenType::CHANGE_EMAIL))->fetch();
        parent::render();
    }

    protected function getTemplatePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'layout.email.latte';
    }

    /**
     * @throws BadTypeException
     * @throws OmittedControlException
     */
    protected function configureForm(Form $form): void
    {
        $newEmailControl = $this->reflectionFormFactory->createField('person_info', 'email');
        $uniqueEmail = new UniqueEmail($this->container);
        $newEmailControl->addRule(
            fn(BaseControl $baseControl) => ($uniqueEmail)($baseControl),
            _('This email is already in use.')
        );
        $form->addComponent($newEmailControl, 'new_email');
    }

    protected function appendSubmitButton(Form $form): SubmitButton
    {
        return $form->addSubmit('submit', _('Change email'));
    }

    /**
     * @throws BadTypeException
     * @throws ChangeInProgressException
     */
    protected function handleFormSuccess(Form $form): void
    {
        /** @phpstan-var array{new_email:string} $values */
        $values = $form->getValues('array');
        $this->sendChangeEmail(
            $this->model,
            $values['new_email'],
            Language::tryFrom($this->translator->lang)
        );
        $this->getPresenter()->flashMessage(
            _(
                'Email with a verification link has been sent to the new email address,' .
                ' the link is active for 20 minutes.'
            ),
            Message::LVL_SUCCESS
        );
        $this->getPresenter()->redirect('this');
    }

    protected function setDefaults(Form $form): void
    {
        $form->setDefaults(['new_email' => $this->model->getInfo() ? $this->model->getInfo()->email : null]);
    }

    /**
     * @throws BadTypeException
     * @throws ChangeInProgressException
     */
    private function sendChangeEmail(PersonModel $person, string $newEmail, Language $lang): void
    {
        self::logEmailChange($person, $newEmail, true);
        $login = $person->getLogin();
        if (!$login) {
            $this->loginService->createLogin($person);
        }
        $token = $login->getActiveTokens(AuthTokenType::from(AuthTokenType::CHANGE_EMAIL))->fetch();
        if ($token) {
            throw new ChangeInProgressException();
        }
        $token = $this->authTokenService->createToken(
            $login,
            AuthTokenType::from(AuthTokenType::CHANGE_EMAIL),
            (new \DateTime())->modify('+20 minutes'),
            $newEmail
        );
        $oldData = array_merge(
            $this->templateFactory->renderWithParameters(
                __DIR__ . '/email.old.latte',
                ['lang' => $lang, 'person' => $person, 'newEmail' => $newEmail,],
                $lang
            ),
            [
                'sender' => 'FKSDB <fksdb@fykos.cz>',
                'recipient' => (string)$person->getInfo()->email,
                'topic' => EmailMessageTopic::from(EmailMessageTopic::Internal),
                'lang' => $lang,
            ]
        );
        $newData = array_merge(
            $this->templateFactory->renderWithParameters(
                __DIR__ . '/email.new.latte',
                ['lang' => $lang, 'person' => $person, 'newEmail' => $newEmail, 'token' => $token,],
                $lang
            ),
            [
                'sender' => 'FKSDB <fksdb@fykos.cz>',
                'recipient' => $newEmail,
                'topic' => EmailMessageTopic::from(EmailMessageTopic::Internal),
                'lang' => $lang,
            ]
        );
        $this->emailMessageService->addMessageToSend($oldData);
        $this->emailMessageService->addMessageToSend($newData);
    }

    public static function logEmailChange(PersonModel $person, string $newEmail, bool $request): void
    {
        Debugger::log(
            sprintf(
                $request
                    ? 'request: person %d (%s) old: "%s" new: "%s"'
                    : 'change: person %d (%s) old: "%s" new: "%s"',
                $person->person_id,
                $person->getFullName(),
                $person->getInfo()->email,
                $newEmail
            ),
            'email-change'
        );
    }
}

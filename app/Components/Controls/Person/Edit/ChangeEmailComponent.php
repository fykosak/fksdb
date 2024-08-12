<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Person\Edit;

use FKSDB\Components\EntityForms\EntityFormComponent;
use FKSDB\Components\Forms\Rules\UniqueEmail;
use FKSDB\Models\Authentication\Exceptions\ChangeInProgressException;
use FKSDB\Models\Email\Source\ChangeEmail\ChangeEmailEmail;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Columns\OmittedControlException;
use FKSDB\Models\ORM\Models\AuthTokenModel;
use FKSDB\Models\ORM\Models\AuthTokenType;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\ReflectionFactory;
use FKSDB\Models\ORM\Services\LoginService;
use FKSDB\Modules\Core\Language;
use Fykosak\Utils\Logging\Message;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;
use Tracy\Debugger;

/**
 * @phpstan-extends EntityFormComponent<PersonModel>
 * @property-read PersonModel $model
 */
class ChangeEmailComponent extends EntityFormComponent
{
    private ReflectionFactory $reflectionFormFactory;
    private LoginService $loginService;

    public function inject(
        ReflectionFactory $reflectionFormFactory,
        LoginService $loginService
    ): void {
        $this->reflectionFormFactory = $reflectionFormFactory;
        $this->loginService = $loginService;
    }

    public function render(): void
    {
        $login = $this->model->getLogin();
        $this->template->lang = Language::tryFrom($this->translator->lang);
        /** @var AuthTokenModel|null $token */
        $this->template->changeActive = $login
            && $login->hasActiveToken(AuthTokenType::from(AuthTokenType::ChangeEmail));
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
     * @throws \Throwable
     */
    protected function handleFormSuccess(Form $form): void
    {
        /** @phpstan-var array{new_email:string} $values */
        $values = $form->getValues('array');
        $lang = Language::from($this->translator->lang);
        $newEmail = $values['new_email'];
        self::logEmailChange($this->model, $newEmail, true);
        $login = $this->model->getLogin();
        if (!$login) {
            $this->loginService->createLogin($this->model);
        }
        if ($login->hasActiveToken(AuthTokenType::from(AuthTokenType::ChangeEmail))) {
            throw new ChangeInProgressException();
        }
        $emailSource = new ChangeEmailEmail($this->container);
        $emailSource->createAndSend(['lang' => $lang, 'person' => $this->model, 'newEmail' => $newEmail]);

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

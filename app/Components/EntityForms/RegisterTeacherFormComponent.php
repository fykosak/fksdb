<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms;

use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Forms\Containers\SearchContainer\PersonSearchContainer;
use FKSDB\Components\Forms\Controls\CaptchaBox;
use FKSDB\Components\Forms\Controls\ReferencedId;
use FKSDB\Models\Authentication\AccountManager;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Columns\OmittedControlException;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\TeacherModel;
use FKSDB\Models\ORM\Services\TeacherService;
use FKSDB\Models\Persons\Resolvers\SelfPersonResolver;
use FKSDB\Modules\Core\Language;
use Fykosak\Utils\Logging\Message;
use Nette\Application\ForbiddenRequestException;
use Nette\DI\Container;
use Nette\Forms\Form;

/**
 * @phpstan-extends EntityFormComponent<TeacherModel>
 */
class RegisterTeacherFormComponent extends EntityFormComponent
{
    use ReferencedPersonTrait;

    public const CONT_TEACHER = 'teacher';

    private ?PersonModel $loggedPerson;
    private AccountManager $accountManager;
    private TeacherService $teacherService;

    public function __construct(
        Container $container,
        ?PersonModel $person
    ) {
        parent::__construct($container, null);
        $this->loggedPerson = $person;
    }

    final public function inject(
        AccountManager $accountManager,
        TeacherService $teacherService
    ): void {
        $this->accountManager = $accountManager;
        $this->teacherService = $teacherService;
    }

    /**
     * @throws BadTypeException
     * @throws OmittedControlException
     * @throws ForbiddenRequestException
     */
    protected function configureForm(Form $form): void
    {
        $container = new ModelContainer($this->container, 'teacher');

        $referencedId = $this->referencedPersonFactory->createReferencedPerson(
            $this->getContext()->getParameters()['forms']['registerTeacher'],
            null,
            PersonSearchContainer::SEARCH_EMAIL,
            false,
            new SelfPersonResolver($this->loggedPerson)
        );
        $container->addComponent($referencedId, 'person_id');
        $container->addField('active');
        $form->addComponent($container, self::CONT_TEACHER);
        if (!$this->loggedPerson) {
            $captcha = new CaptchaBox();
            $form->addComponent($captcha, 'captcha');
        }

        $form->addProtection(_('The form has expired. Please send it again.'));
    }

    protected function handleFormSuccess(Form $form): void
    {
        $values = $form->getValues('array');
        /** @phpstan-var ReferencedId<PersonModel> $referencedId */
        $referencedId = $form[self::CONT_TEACHER]['person_id'];
        /** @var PersonModel $person */
        $person = $referencedId->getModel();

        $this->teacherService->storeModel($values, $person->getTeacher());
        if (!$person->getLogin()) {
            try {
                $this->accountManager->sendLoginWithInvitation(
                    $person,
                    Language::from($this->translator->lang)
                );
                $this->getPresenter()->flashMessage(_('E-mail invitation sent.'), Message::LVL_INFO);
            } catch (\Throwable $exception) {
                $this->getPresenter()->flashMessage(_('E-mail invitation failed to sent.'), Message::LVL_ERROR);
            }
        }
        $this->getPresenter()->redirect(':Core:Dispatch:default');
    }

    protected function setDefaults(Form $form): void
    {
        $form->setDefaults([
            self::CONT_TEACHER => [
                'person_id' => isset($this->loggedPerson)
                    ? $this->loggedPerson->person_id
                    : null,
            ],
        ]);
    }
}

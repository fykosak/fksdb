<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms;

use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Columns\OmittedControlException;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\PersonInfoService;
use FKSDB\Models\ORM\Services\PersonService;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Logging\Message;
use Nette\Application\ForbiddenRequestException;
use Nette\DI\Container;
use Nette\Forms\Form;
use Nette\InvalidArgumentException;

/**
 * @phpstan-extends ModelForm<PersonModel,array{
 *      person_info: array<string,mixed>,
 *      person: array{gender?:string|null,family_name:string}
 *  }>
 */
class PersonFormComponent extends ModelForm
{
    public const PERSON_CONTAINER = 'person';
    public const PERSON_INFO_CONTAINER = 'person_info';

    private PersonService $personService;
    private PersonInfoService $personInfoService;
    private FieldLevelPermission $userPermission;

    public function __construct(Container $container, int $userPermission, ?PersonModel $person)
    {
        parent::__construct($container, $person);
        $this->userPermission = new FieldLevelPermission($userPermission, $userPermission);
    }

    final public function injectFactories(
        PersonService $personService,
        PersonInfoService $personInfoService
    ): void {
        $this->personService = $personService;
        $this->personInfoService = $personInfoService;
    }

    /**
     * @throws BadTypeException
     * @throws OmittedControlException
     * @throws ForbiddenRequestException
     */
    protected function configureForm(Form $form): void
    {
        $fields = $this->getContext()->getParameters()['forms']['adminPerson'];
        foreach ($fields as $table => $rows) {
            switch ($table) {
                case self::PERSON_INFO_CONTAINER:
                case self::PERSON_CONTAINER:
                    $control = new ModelContainer($this->container, $table);
                    foreach ($rows as $field => $metadata) {
                        $control->addField($field, $metadata, $this->userPermission);
                    }
                    break;
                default:
                    throw new InvalidArgumentException();
            }
            $form->addComponent($control, $table);
        }
    }

    protected function setDefaults(Form $form): void
    {
        if (isset($this->model)) {
            $form->setDefaults([
                self::PERSON_CONTAINER => $this->model->toArray(),
                self::PERSON_INFO_CONTAINER => $this->model->getInfo() ? $this->model->getInfo()->toArray() : null,
            ]);
        }
    }

    protected function innerSuccess(array $values, Form $form): Model
    {
        $person = $this->personService->storeModel($values[self::PERSON_CONTAINER], $this->model);
        $this->personInfoService->storeModel(
            array_merge($values[self::PERSON_INFO_CONTAINER], ['person_id' => $person->person_id]),
            $person->getInfo()
        );
        return $person;
    }

    protected function successRedirect(Model $model): void
    {
        $this->getPresenter()->flashMessage(
            isset($this->model) ? _('Person has been updated') : _('Person has been created'),
            Message::LVL_SUCCESS
        );
        $this->getPresenter()->redirect('this');
    }
}

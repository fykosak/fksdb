<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms;

use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Columns\OmittedControlException;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\FieldLevelPermissionValue;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\PersonInfoService;
use FKSDB\Models\ORM\Services\PersonService;
use FKSDB\Models\Utils\FormUtils;
use Fykosak\Utils\Logging\FlashMessageDump;
use Fykosak\Utils\Logging\MemoryLogger;
use Fykosak\Utils\Logging\Message;
use Nette\Application\ForbiddenRequestException;
use Nette\DI\Container;
use Nette\Forms\Form;
use Nette\InvalidArgumentException;

/**
 * @phpstan-extends EntityFormComponent<PersonModel>
 */
class PersonFormComponent extends EntityFormComponent
{
    public const PERSON_CONTAINER = 'person';
    public const PERSON_INFO_CONTAINER = 'person_info';

    private PersonService $personService;
    private PersonInfoService $personInfoService;
    private MemoryLogger $logger;
    private FieldLevelPermission $userPermission;

    public function __construct(Container $container, FieldLevelPermissionValue $userPermission, ?PersonModel $person)
    {
        parent::__construct($container, $person);
        $this->userPermission = new FieldLevelPermission($userPermission, $userPermission);
        $this->logger = new MemoryLogger();
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

    protected function handleFormSuccess(Form $form): void
    {
        $connection = $this->personService->explorer->getConnection();
        /** @phpstan-var array{
         *     person_info: array<string,mixed>,
         *     person: array{gender?:string|null,family_name:string}
         * } $values
         */
        $values = $form->getValues('array');
        $data = FormUtils::emptyStrToNull2($values);
        $connection->beginTransaction();
        $this->logger->clear();
        $person = $this->personService->storeModel($data[self::PERSON_CONTAINER], $this->model);
        $this->personInfoService->storeModel(
            array_merge($data[self::PERSON_INFO_CONTAINER], ['person_id' => $person->person_id]),
            $person->getInfo()
        );

        $connection->commit();
        $this->logger->log(
            new Message(
                isset($this->model) ? _('Data has been updated') : _('Person has been created'),
                Message::LVL_SUCCESS
            )
        );
        FlashMessageDump::dump($this->logger, $this->getPresenter());
        $this->getPresenter()->redirect('this');
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
}

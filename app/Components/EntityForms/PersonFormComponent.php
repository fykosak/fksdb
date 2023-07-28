<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms;

use FKSDB\Components\Forms\Factories\SingleReflectionFormFactory;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\OmittedControlException;
use FKSDB\Models\ORM\Services\PersonInfoService;
use FKSDB\Models\ORM\Services\PersonService;
use FKSDB\Models\Utils\FormUtils;
use Fykosak\Utils\Logging\FlashMessageDump;
use Fykosak\Utils\Logging\MemoryLogger;
use Fykosak\Utils\Logging\Message;
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

    private SingleReflectionFormFactory $singleReflectionFormFactory;
    private PersonService $personService;
    private PersonInfoService $personInfoService;
    private MemoryLogger $logger;
    private FieldLevelPermission $userPermission;

    public function __construct(Container $container, int $userPermission, ?PersonModel $person)
    {
        parent::__construct($container, $person);
        $this->userPermission = new FieldLevelPermission($userPermission, $userPermission);
        $this->logger = new MemoryLogger();
    }

    final public function injectFactories(
        SingleReflectionFormFactory $singleReflectionFormFactory,
        PersonService $personService,
        PersonInfoService $personInfoService
    ): void {
        $this->singleReflectionFormFactory = $singleReflectionFormFactory;
        $this->personService = $personService;
        $this->personInfoService = $personInfoService;
    }

    /**
     * @throws BadTypeException
     * @throws OmittedControlException
     */
    protected function configureForm(Form $form): void
    {
        $fields = $this->getContext()->getParameters()['forms']['adminPerson'];
        foreach ($fields as $table => $rows) {
            switch ($table) {
                case self::PERSON_INFO_CONTAINER:
                case self::PERSON_CONTAINER:
                    $control = $this->singleReflectionFormFactory->createContainerWithMetadata(
                        $table,
                        $rows,
                        $this->userPermission
                    );
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
        $values = $form->getValues();
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

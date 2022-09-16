<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms;

use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Models\Authorization\ContestAuthorizator;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Services\ContestantService;
use FKSDB\Models\Persons\AclResolver;
use Fykosak\Utils\Logging\Message;
use Nette\DI\Container;
use Nette\Forms\Form;

/**
 * @property ContestantModel $model
 */
class ContestantFormComponent extends EntityFormComponent
{
    use ReferencedPersonTrait;

    public const CONT_CONTESTANT = 'contestant';

    private ContestYearModel $contestYear;
    private ContestAuthorizator $contestAuthorizator;
    private ContestantService $service;

    public function __construct(ContestYearModel $contestYear, Container $container, ?ContestantModel $model)
    {
        parent::__construct($container, $model);
        $this->contestYear = $contestYear;
    }

    public function inject(
        ContestantService $service,
        ContestAuthorizator $contestAuthorizator
    ): void {
        $this->contestAuthorizator = $contestAuthorizator;
        $this->service = $service;
    }

    protected function configureForm(Form $form): void
    {
        $container = new ModelContainer($this->container);
        $referencedId = $this->createPersonId(
            $this->contestYear,
            $this->isCreating(),
            new AclResolver($this->contestAuthorizator, $this->contestYear->contest),
            new AclResolver($this->contestAuthorizator, $this->contestYear->contest),
            $this->getContext()->getParameters()['forms']['adminContestant']
        );
        $container->addComponent($referencedId, 'person_id');
        $form->addComponent($container, self::CONT_CONTESTANT);
    }

    protected function handleFormSuccess(Form $form): void
    {
        $values = $form->getValues();
        if ($this->isCreating()) {
            $this->service->storeModel([
                'contest_id' => $this->contestYear->contest_id,
                'year' => $this->contestYear->year,
                'person_id' => $values[self::CONT_CONTESTANT]['person_id'],
            ]);
        }
        $this->getPresenter()->flashMessage(
            isset($this->model) ? _('Contestant has been updated') : _('Contestant has been created'),
            Message::LVL_SUCCESS
        );
        $this->getPresenter()->redirect('list');
    }

    /**
     * @throws BadTypeException
     */
    protected function setDefaults(): void
    {
        $this->getForm()->setDefaults([
            self::CONT_CONTESTANT => ['person_id' => $this->model->person_id],
        ]);
    }
}

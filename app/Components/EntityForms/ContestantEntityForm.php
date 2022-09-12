<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms;

use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Forms\Containers\SearchContainer\PersonSearchContainer;
use FKSDB\Components\Forms\Factories\ReferencedPerson\ReferencedPersonFactory;
use FKSDB\Models\Authorization\ContestAuthorizator;
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
class ContestantEntityForm extends EntityFormComponent
{

    public const CONT_CONTESTANT = 'contestant';

    private ContestYearModel $contestYear;
    private ReferencedPersonFactory $referencedPersonFactory;
    private ContestAuthorizator $contestAuthorizator;
    private ContestantService $service;

    public function __construct(ContestYearModel $contestYear, Container $container, ?ContestantModel $model)
    {
        parent::__construct($container, $model);
        $this->contestYear = $contestYear;
    }

    public function inject(
        ContestantService $service,
        ReferencedPersonFactory $referencedPersonFactory,
        ContestAuthorizator $contestAuthorizator
    ): void {
        $this->referencedPersonFactory = $referencedPersonFactory;
        $this->contestAuthorizator = $contestAuthorizator;
        $this->service = $service;
    }

    protected function configureForm(Form $form): void
    {
        $container = new ModelContainer($this->container);
        $referencedId = $this->referencedPersonFactory->createReferencedPerson(
            $this->getContext()->getParameters()[$this->contestYear->contest->getContestSymbol()]['adminContestant'],
            $this->contestYear,
            PersonSearchContainer::SEARCH_ID,
            $this->isCreating(),
            new AclResolver($this->contestAuthorizator, $this->contestYear->contest),
            new AclResolver($this->contestAuthorizator, $this->contestYear->contest)
        );
        $referencedId->addRule(Form::FILLED, _('Person is required.'));
        $referencedId->getReferencedContainer()->setOption('label', _('Person'));
        $referencedId->getSearchContainer()->setOption('label', _('Person'));
        $container->addComponent($referencedId, 'person_id');
        $form->addComponent($container, self::CONT_CONTESTANT);
    }

    protected function handleFormSuccess(Form $form): void
    {
        $values = $form->getValues();
        if (isset($model)) {
            // do nothing RP saved model
        } else {
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

    protected function setDefaults(): void
    {
        $this->getForm()->setDefaults([
            self::CONT_CONTESTANT => ['person' => $this->model->person_id],
        ]);
    }
}

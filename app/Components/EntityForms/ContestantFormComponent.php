<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms;

use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Components\Forms\Controls\ReferencedId;
use FKSDB\Models\Authorization\ContestAuthorizator;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\Persons\Resolvers\AclResolver;
use FKSDB\Models\Results\ResultsModelFactory;
use Fykosak\Utils\Logging\Message;
use Nette\Application\BadRequestException;
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

    public function __construct(ContestYearModel $contestYear, Container $container, ?ContestantModel $model)
    {
        parent::__construct($container, $model);
        $this->contestYear = $contestYear;
    }

    public function inject(ContestAuthorizator $contestAuthorizator): void
    {
        $this->contestAuthorizator = $contestAuthorizator;
    }

    protected function configureForm(Form $form): void
    {
        $container = new ContainerWithOptions($this->container);
        $referencedId = $this->createPersonId(
            $this->contestYear,
            $this->isCreating(),
            new AclResolver($this->contestAuthorizator, $this->contestYear->contest),
            $this->getContext()->getParameters()['forms']['adminContestant']
        );
        $container->addComponent($referencedId, 'person_id');
        $form->addComponent($container, self::CONT_CONTESTANT);
    }

    /**
     * @throws BadRequestException
     */
    protected function handleFormSuccess(Form $form): void
    {
        $strategy = ResultsModelFactory::findEvaluationStrategy($this->getContext(), $this->contestYear);
        if (isset($this->model)) {
            $strategy->updateCategory($this->model);
        } else {
            /** @var ReferencedId $referencedId */
            $referencedId = $form[self::CONT_CONTESTANT]['person_id'];
            /** @var PersonModel $person */
            $person = $referencedId->getModel();
            $strategy->createContestant($person);
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

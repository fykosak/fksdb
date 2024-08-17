<?php

declare(strict_types=1);

namespace FKSDB\Components\Contestants;

use FKSDB\Components\EntityForms\ModelForm;
use FKSDB\Components\EntityForms\ReferencedPersonTrait;
use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Components\Forms\Controls\ReferencedId;
use FKSDB\Models\Authorization\Authorizators\ContestAuthorizator;
use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\Persons\Resolvers\AclResolver;
use FKSDB\Models\Results\ResultsModelFactory;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Logging\Message;
use Nette\Application\BadRequestException;
use Nette\DI\Container;
use Nette\Forms\Form;

/**
 * @phpstan-extends ModelForm<ContestantModel,array{}>
 */
final class ContestantForm extends ModelForm
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
            !isset($this->model),
            new AclResolver($this->contestAuthorizator, $this->contestYear->contest),
            $this->getContext()->getParameters()['forms']['adminContestant']
        );
        $container->addComponent($referencedId, 'person_id');
        $form->addComponent($container, self::CONT_CONTESTANT);
    }

    protected function setDefaults(Form $form): void
    {
        $form->setDefaults([
            self::CONT_CONTESTANT => ['person_id' => $this->model->person_id],
        ]);
    }

    /**
     * @throws BadRequestException
     */
    protected function innerSuccess(array $values, Form $form): ContestantModel
    {
        $form->getValues(); // trigger RPC
        $strategy = ResultsModelFactory::findEvaluationStrategy($this->getContext(), $this->contestYear);
        if (isset($this->model)) {
            return $strategy->updateCategory($this->model);
        } else {
            /** @phpstan-var ReferencedId<PersonModel> $referencedId */
            $referencedId = $form[self::CONT_CONTESTANT]['person_id'];//@phpstan-ignore-line
            $person = $referencedId->getModel();
            return $strategy->createContestant($person);
        }
    }

    protected function successRedirect(Model $model): void
    {
        $this->getPresenter()->flashMessage(
            isset($this->model) ? _('Contestant has been updated') : _('Contestant has been created'),
            Message::LVL_SUCCESS
        );
        $this->getPresenter()->redirect('list');
    }
}

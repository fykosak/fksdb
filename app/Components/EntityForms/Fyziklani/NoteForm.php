<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms\Fyziklani;

use FKSDB\Components\EntityForms\EntityFormComponent;
use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Columns\OmittedControlException;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Services\Fyziklani\TeamService2;
use Fykosak\Utils\Logging\Message;
use Nette\Application\ForbiddenRequestException;
use Nette\DI\Container;
use Nette\Forms\Form;
use Tracy\Debugger;

/**
 * @phpstan-extends EntityFormComponent<TeamModel2>
 */
final class NoteForm extends EntityFormComponent
{
    private TeamService2 $teamService;

    public function __construct(
        Container $container,
        TeamModel2 $model
    ) {
        parent::__construct($container, $model);
    }

    final public function injectPrimary(TeamService2 $teamService): void
    {
        $this->teamService = $teamService;
    }

    /**
     * @throws BadTypeException
     * @throws OmittedControlException
     * @throws ForbiddenRequestException
     */
    protected function configureForm(Form $form): void
    {
        $container = new ModelContainer($this->container, 'fyziklani_team');
        $container->addField('note', ['required' => false]);
        $container->addField('internal_note', ['required' => false]);
        $form->addComponent($container, 'team');
    }

    protected function handleFormSuccess(Form $form): void
    {
        /** @phpstan-var array{team:array{note:string,internal_note:string}} $values */
        $values = $form->getValues('array');
        $this->teamService->storeModel($values['team'], $this->model);
        Debugger::log(json_encode($values), 'team-notes');
        $this->getPresenter()->flashMessage(_('Notes has been updated'), Message::LVL_SUCCESS);
        $this->getPresenter()->redirect('this');
    }

    protected function setDefaults(Form $form): void
    {
        $form->setDefaults(['team' => $this->model->toArray()]);
    }
}

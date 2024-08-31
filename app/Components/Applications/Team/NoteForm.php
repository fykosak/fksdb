<?php

declare(strict_types=1);

namespace FKSDB\Components\Applications\Team;

use FKSDB\Components\EntityForms\ModelForm;
use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Columns\OmittedControlException;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Services\Fyziklani\TeamService2;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Logging\Message;
use Nette\Application\ForbiddenRequestException;
use Nette\DI\Container;
use Nette\Forms\Form;
use Tracy\Debugger;

/**
 * @phpstan-extends ModelForm<TeamModel2,array{team:array{note:string,internal_note:string}}>
 */
final class NoteForm extends ModelForm
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

    protected function setDefaults(Form $form): void
    {
        $form->setDefaults(['team' => $this->model->toArray()]);
    }

    protected function innerSuccess(array $values, Form $form): TeamModel2
    {
        /** @var TeamModel2 $team */
        $team = $this->teamService->storeModel($values['team'], $this->model);
        Debugger::log(json_encode($values), 'team-notes');
        return $team;
    }

    protected function successRedirect(Model $model): void
    {
        $this->getPresenter()->flashMessage(_('Notes has been updated'), Message::LVL_SUCCESS);
        $this->getPresenter()->redirect('this');
    }
}

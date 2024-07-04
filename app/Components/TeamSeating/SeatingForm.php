<?php

declare(strict_types=1);

namespace FKSDB\Components\TeamSeating;

use FKSDB\Components\Controls\FormComponent\FormComponent;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Services\Fyziklani\TeamService2;
use FKSDB\Models\Utils\FormUtils;
use Fykosak\Utils\Logging\Message;
use Nette\DI\Container;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;

class SeatingForm extends FormComponent
{
    private EventModel $event;
    private TeamService2 $service;

    public function __construct(Container $container, EventModel $event)
    {
        parent::__construct($container);
        $this->event = $event;
    }

    public function inject(TeamService2 $service): void
    {
        $this->service = $service;
    }

    public function render(): void
    {
        $this->setDefaults($this->getForm());
        parent::render();
    }

    protected function handleSuccess(Form $form): void
    {
        /** @phpstan-var  array<int,string> $values */
        $values = $form->getValues('array');
        foreach (FormUtils::emptyStrToNull2($values) as $teamId => $value) {
            $team = $this->service->findByPrimary($teamId);
            $this->service->storeModel(['place' => $value], $team);
        }
        $this->flashMessage('Data saved', Message::LVL_SUCCESS);
        $this->getPresenter()->redirect('this');
    }

    protected function appendSubmitButton(Form $form): SubmitButton
    {
        return $form->addSubmit('submit', _('button.save'));
    }

    protected function configureForm(Form $form): void
    {
        /** @var TeamModel2 $team */
        foreach ($this->event->getTeams()->order('fyziklani_team_id') as $team) {
            $form->addText(
                (string)$team->fyziklani_team_id,
                sprintf(
                    '(%d) %s - %s - %s',
                    $team->fyziklani_team_id,
                    $team->name,
                    $team->game_lang->value,
                    $team->category->value
                )
            );
        }
    }

    private function setDefaults(Form $form): void
    {
        $defaults = [];
        /** @var TeamModel2 $team */
        foreach ($this->event->getTeams() as $team) {
            $defaults[$team->getPrimary()] = $team->place;
        }
        $form->setDefaults($defaults);
    }
}

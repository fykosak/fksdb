<?php

declare(strict_types=1);

namespace FKSDB\Components\Game\Closing;

use FKSDB\Components\Controls\FormComponent\FormComponent;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use Fykosak\Utils\Logging\Message;
use Nette\DI\Container;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;

class TeamSelectForm extends FormComponent
{
    private EventModel $event;

    public function __construct(Container $container, EventModel $event)
    {
        parent::__construct($container);
        $this->event = $event;
    }

    protected function handleSuccess(SubmitButton $button): void
    {
        $values = $button->getForm()->getValues('array');
        /** @var TeamModel2|null $team */
        $team = $this->event->getTeams()->where('fyziklani_team_id', +$values['team_id'])->fetch();
        if ($team) {
            $this->getPresenter()->redirect(':Game:Close:team', ['id' => $team->fyziklani_team_id]);
        }
        $this->getPresenter()->flashMessage(_('Team does not exists'), Message::LVL_ERROR);
    }

    protected function appendSubmitButton(Form $form): SubmitButton
    {
        return $form->addSubmit('submit', _('Find and close'));
    }

    protected function configureForm(Form $form): void
    {
        $form->addText('team_id', _('Team Id'));
    }
}

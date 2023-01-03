<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Application;

use FKSDB\Components\Grids\Components\FilterGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\FieldLevelPermissionValue;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamState;
use Nette\Database\Table\Selection;
use Nette\DI\Container;
use Nette\Forms\Form;

class TeamApplicationsGrid extends FilterGrid
{
    protected EventModel $event;

    public function __construct(EventModel $event, Container $container)
    {
        parent::__construct($container);
        $this->event = $event;
    }

    protected function getModels(): Selection
    {
        $query = $this->event->getTeams();
        if (!isset($this->filterParams)) {
            return $query;
        }
        foreach ($this->filterParams as $key => $filterParam) {
            switch ($key) {
                case 'status':
                    $query->where('state', $filterParam);
            }
        }
        return $query;
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function configure(): void
    {
        $this->paginate = false;
        $this->addColumns([
            'fyziklani_team.fyziklani_team_id',
            'fyziklani_team.name',
            'fyziklani_team.state',
            'fyziklani_team.game_lang',
            'fyziklani_team.category',
            'fyziklani_team.force_a',
            'fyziklani_team.phone',
        ]);
        $this->addPresenterButton('detail', 'detail', _('Detail'), false, ['id' => 'fyziklani_team_id']);
        //$this->addCSVDownloadButton();
    }

    /**
     * @throws NotImplementedException
     */
    protected function configureForm(Form $form): void
    {
        $items = [];
        foreach (TeamState::cases() as $state) {
            $items[$state->value] = $state->label();
        }
        $form->addSelect('status', _('State'), $items)->setPrompt(_('Select state'));
    }
}

<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Application;

use FKSDB\Components\Grids\Components\FilterGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\Fyziklani\TeamState;
use Fykosak\NetteORM\TypedGroupedSelection;
use Nette\DI\Container;
use Nette\Forms\Form;

/**
 * @phpstan-extends FilterGrid<TeamModel2,array{
 *     status?:string,
 * }>
 */
class TeamApplicationsGrid extends FilterGrid
{
    protected EventModel $event;

    public function __construct(EventModel $event, Container $container)
    {
        parent::__construct($container);
        $this->event = $event;
    }

    /**
     * @phpstan-return TypedGroupedSelection<TeamModel2>
     */
    protected function getModels(): TypedGroupedSelection
    {
        $query = $this->event->getTeams();
        foreach ($this->filterParams as $key => $filterParam) {
            if (!$filterParam) {
                continue;
            }
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

    protected function configureForm(Form $form): void
    {
        $items = [];
        foreach (TeamState::cases() as $state) {
            $items[$state->value] = $state->label();
        }
        $form->addSelect('status', _('State'), $items)->setPrompt(_('Select state'));
    }
}

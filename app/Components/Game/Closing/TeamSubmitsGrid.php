<?php

declare(strict_types=1);

namespace FKSDB\Components\Game\Closing;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Services\Fyziklani\SubmitService;
use Nette\DI\Container;

class TeamSubmitsGrid extends BaseGrid
{
    protected SubmitService $submitService;
    private TeamModel2 $team;

    public function __construct(TeamModel2 $team, Container $container)
    {
        $this->team = $team;
        parent::__construct($container);
    }

    final public function injectServiceFyziklaniSubmit(SubmitService $submitService): void
    {
        $this->submitService = $submitService;
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function configure(): void
    {
        $this->data = $this->team->getSubmits()->order('fyziklani_submit.created');
        $this->paginate = false;

        $this->addColumns([
            'fyziklani_team.name',
            'fyziklani_task.label',
            'fyziklani_submit.points',
            'fyziklani_submit.created',
            'fyziklani_submit.state',
        ]);
        if ($this->team->event->event_type_id === 1) {
            $this->addPresenterButton(':Game:Submit:edit', 'edit', _('Edit'), false, ['id' => 'fyziklani_submit_id']);
            $this->addPresenterButton(
                ':Game:Submit:detail',
                'detail',
                _('Detail'),
                false,
                ['id' => 'fyziklani_submit_id']
            );
        }
    }
}

<?php

declare(strict_types=1);

namespace FKSDB\Components\Game\Closing;

use FKSDB\Components\Grids\Components\BaseGrid;
use FKSDB\Models\ORM\Models\Fyziklani\SubmitModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Services\Fyziklani\SubmitService;
use Fykosak\NetteORM\Selection\TypedGroupedSelection;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;

/**
 * @phpstan-extends BaseGrid<SubmitModel,array{}>
 */
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
     * @phpstan-return TypedGroupedSelection<SubmitModel>
     */
    protected function getModels(): TypedGroupedSelection
    {
        return $this->team->getSubmits()->order('fyziklani_submit.modified');
    }

    protected function configure(): void
    {
        $this->paginate = false;
        $this->addSimpleReferencedColumns([
            '@fyziklani_team.name',
            '@fyziklani_task.label',
            '@fyziklani_submit.points',
            '@fyziklani_submit.created',
            '@fyziklani_submit.modified',
            '@fyziklani_submit.checked',
            '@fyziklani_submit.state',
        ]);
        if ($this->team->event->event_type_id === 1) {
            $this->addPresenterButton(
                ':Game:Submit:edit',
                'edit',
                new Title(null, _('button.edit')),
                false,
                ['id' => 'fyziklani_submit_id']
            );
        }
    }
}

<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids;

use FKSDB\Components\Grids\Components\BaseGrid;
use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Models\SchoolModel;
use FKSDB\Models\ORM\Services\ContestantService;
use Fykosak\NetteORM\Selection\TypedSelection;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;

/**
 * @phpstan-extends BaseGrid<ContestantModel,array{}>
 */
final class ContestantsFromSchoolGrid extends BaseGrid
{
    private SchoolModel $school;
    private ContestantService $service;

    public function __construct(SchoolModel $school, Container $container)
    {
        parent::__construct($container);
        $this->school = $school;
    }

    public function inject(ContestantService $service): void
    {
        $this->service = $service;
    }

    /**
     * @phpstan-return TypedSelection<ContestantModel>
     */
    protected function getModels(): TypedSelection
    {
        return $this->service->getTable()->where(
            'person:person_history.school_id',
            $this->school->school_id
        );
    }

    protected function configure(): void
    {
        $this->paginate = false;
        $this->filtered = false;
        $this->counter = true;
        $this->addSimpleReferencedColumns([
            '@person.full_name',
            '@contestant.year',
            '@person_history.study_year_new',
            '@contest.contest',
        ]);
        $this->addPresenterButton(
            ':Organizer:Contestant:edit',
            'edit',
            new Title(null, _('button.edit')),
            false,
            ['id' => 'contestant_id']
        );
        $this->addPresenterButton(
            ':Organizer:Contestant:detail',
            'detail',
            new Title(null, _('button.detail')),
            false,
            ['id' => 'contestant_id']
        );
    }
}

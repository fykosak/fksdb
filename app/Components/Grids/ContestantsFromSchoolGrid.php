<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids;

use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Models\SchoolModel;
use FKSDB\Models\ORM\Services\ContestantService;
use Nette\DI\Container;

/**
 * @phpstan-extends EntityGrid<ContestantModel>
 */
class ContestantsFromSchoolGrid extends EntityGrid
{
    public function __construct(SchoolModel $school, Container $container)
    {
        parent::__construct($container, ContestantService::class, [
            'person.full_name',
            'contestant.year',
            'person_history.study_year',
            'contest.contest',
        ], [
            'person:person_history.school_id' => $school->school_id,
        ]);
    }

    protected function configure(): void
    {
        $this->addPresenterButton(':Org:Contestant:edit', 'edit', _('Edit'), false, ['id' => 'contestant_id']);
        $this->addPresenterButton(':Org:Contestant:detail', 'detail', _('Detail'), false, ['id' => 'contestant_id']);
        $this->paginate = false;
    }
}

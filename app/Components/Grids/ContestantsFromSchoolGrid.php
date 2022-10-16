<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\SchoolModel;
use FKSDB\Models\ORM\Services\ContestantService;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;

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

    /**
     * @throws BadTypeException
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     */
    protected function configure(Presenter $presenter): void
    {
        parent::configure($presenter);
        $this->addLinkButton(':Org:Contestant:edit', 'edit', _('Edit'), false, ['id' => 'contestant_id']);
        $this->addLinkButton(':Org:Contestant:detail', 'detail', _('Detail'), false, ['id' => 'contestant_id']);
        $this->paginate = false;
    }
}

<?php

namespace FKSDB\Components\Grids;

use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\Models\ModelSchool;
use FKSDB\ORM\Services\ServiceContestant;
use Nette\Application\IPresenter;
use Nette\DI\Container;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ContestantsFromSchoolGrid extends EntityGrid {

    public function __construct(ModelSchool $school, Container $container) {
        parent::__construct($container, ServiceContestant::class, [
            'person.full_name',
            'contestant_base.year', /*'person_history.study_year',*/
            'contest.contest',
        ], [
            'person:person_history.school_id' => $school->school_id,
        ]);
    }

    /**
     * @param IPresenter $presenter
     * @return void
     * @throws BadTypeException
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     */
    protected function configure(IPresenter $presenter): void {
        parent::configure($presenter);
        $this->addLinkButton(':Org:Contestant:edit', 'edit', _('Edit'), false, ['id' => 'ct_id']);
        $this->addLinkButton(':Org:Contestant:detail', 'detail', _('Detail'), false, ['id' => 'ct_id']);
        $this->paginate = false;
    }
}

<?php

namespace FKSDB\Components\Grids;

use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\Models\ModelSchool;
use FKSDB\ORM\Services\ServiceContestant;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;
use FKSDB\SQL\ViewDataSource;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ContestantsFromSchoolGrid extends BaseGrid {

    private ServiceContestant $serviceContestant;

    private ModelSchool $school;

    /**
     * ContestantsGrid constructor.
     * @param ModelSchool $school
     * @param Container $container
     */
    public function __construct(ModelSchool $school, Container $container) {
        parent::__construct($container);
        $this->school = $school;
    }

    public function injectServiceContestant(ServiceContestant $serviceContestant): void {
        $this->serviceContestant = $serviceContestant;
    }

    protected function getData(): IDataSource {
        $contestants = $this->serviceContestant->getTable()->where([
            'person:person_history.school_id' => $this->school->school_id,
        ]);
        return new ViewDataSource('ct_id', $contestants);
    }

    /**
     * @param Presenter $presenter
     * @return void
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     * @throws BadTypeException
     */
    protected function configure(Presenter $presenter) {
        parent::configure($presenter);
        $this->addColumns(['person.full_name', 'contestant_base.year', /*'person_history.study_year',*/ 'contest.contest']);

        $this->addLinkButton(':Org:Contestant:edit', 'edit', _('Edit'), false, ['id' => 'ct_id']);
        $this->addLinkButton(':Org:Contestant:detail', 'detail', _('Detail'), false, ['id' => 'ct_id']);

        $this->paginate = false;
    }
}

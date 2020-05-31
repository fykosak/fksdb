<?php

namespace FKSDB\Components\Grids;

use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelSchool;
use FKSDB\ORM\Services\ServiceContestant;
use Nette\DI\Container;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;
use OrgModule\BasePresenter;
use SQL\ViewDataSource;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ContestantsFromSchoolGrid extends BaseGrid {

    /**
     * @var ServiceContestant
     */
    private $serviceContestant;
    /**
     * @var ModelSchool
     */
    private $school;

    /**
     * ContestantsGrid constructor.
     * @param ModelSchool $school
     * @param Container $container
     */
    public function __construct(ModelSchool $school, Container $container) {
        parent::__construct($container);
        $this->school = $school;
    }

    /**
     * @param ServiceContestant $serviceContestant
     * @return void
     */
    public function injectServiceContestant(ServiceContestant $serviceContestant) {
        $this->serviceContestant = $serviceContestant;
    }

    /**
     * @param BasePresenter $presenter
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     */
    protected function configure($presenter) {
        parent::configure($presenter);

        $contestants = $this->serviceContestant->getContext()->table(DbNames::VIEW_CONTESTANT)
            ->select('*')->where([
                'v_contestant.school_id' => $this->school->school_id,
            ]);


        $this->setDataSource(new ViewDataSource('ct_id', $contestants));
        $this->setDefaultOrder('name_lex ASC');

        //
        // columns
        //
        $this->addColumn('name', _('Name'));
        $this->addColumn('study_year', _('Study year'));
        $this->addColumn('contest_id', _('Contest'));
        $this->addColumn('year', 'Contest year');

        //
        // operations
        //
        $this->addButton('editPerson', _('Edit'))
            ->setText(_('Edit'))
            ->setLink(function ($row) use ($presenter) {
                return $presenter->link('Org:Contestant:edit', [
                    'id' => $row->ct_id,
                ]);
            });
        $this->paginate = false;
    }
}

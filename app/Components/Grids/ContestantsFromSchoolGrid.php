<?php

namespace FKSDB\Components\Grids;

use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelSchool;
use FKSDB\ORM\Services\ServiceContestant;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;
use SQL\ViewDataSource;

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
        $contestants = $this->serviceContestant->getContext()->table(DbNames::VIEW_CONTESTANT)
            ->select('*')->where([
                'v_contestant.school_id' => $this->school->school_id,
            ]);

        return new ViewDataSource('ct_id', $contestants);
    }

    /**
     * @param Presenter $presenter
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     */
    protected function configure(Presenter $presenter): void {
        parent::configure($presenter);

        $this->paginate = false;
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

    }
}

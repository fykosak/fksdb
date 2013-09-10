<?php

namespace FKSDB\Components\Grids;

use ModelPerson;
use NiftyGrid\DataSource\NDataSource;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ContestantsGrid extends BaseGrid {

    /**
     * @var ServiceSubmit
     */
    private $serviceSubmit;

    function __construct(ServiceSubmit $serviceSubmit) {
        $this->serviceSubmit = $serviceSubmit;
    }

    protected function configure($presenter) {
        parent::configure($presenter);

        //
        // data
        //
        $serviceContestant = $presenter->context->getService('ServiceContestant');
        $contestants = $serviceContestant->getCurrentContestants($presenter->getSelectedContest()->contest_id, $presenter->getSelectedYear());

        $this->setDataSource(new NDataSource($contestants));
        $this->setDefaultOrder('family_name, other_name ASC');

        //
        // columns
        //
        $this->addColumn('display_name', 'Jméno')->setRenderer(function($row) {
                    $person = ModelPerson::createFromTableRow($row);
                    return $person->getFullname();
                });
        $this->addColumn('study_year', 'Ročník');
        $this->addColumn('school_name', 'Škola');

        //
        // appeareance
        //
        $this->paginate = false;
    }

}

<?php

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class GridContestants extends AbstractGrid {

    protected function configure($presenter) {
        parent::configure($presenter);

        //
        // data
        //
        $serviceContestant = $presenter->context->getService('ServiceContestant');
        $contestants = $serviceContestant->getCurrentContestants($presenter->getSelectedContest()->contest_id, $presenter->getSelectedYear());
        
        $this->setDataSource(new NiftyGrid\DataSource\NDataSource($contestants));
        $this->setDefaultOrder('display_name ASC');

        //
        // columns
        //
        $this->addColumn('display_name', 'Jméno');
        $this->addColumn('study_year', 'Ročník');
        $this->addColumn('school_name', 'Škola');

        //
        // appeareance
        //
        $this->paginate = false;

    }

}

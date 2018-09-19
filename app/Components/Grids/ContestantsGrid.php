<?php

namespace FKSDB\Components\Grids;


use ServiceContestant;
use SQL\ViewDataSource;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ContestantsGrid extends BaseGrid {

    /**
     * @var ServiceContestant
     */
    private $serviceContestant;

    function __construct(ServiceContestant $serviceContestant) {
        parent::__construct();

        $this->serviceContestant = $serviceContestant;
    }

    protected function configure($presenter) {
        parent::configure($presenter);
        $this->setTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'BaseGrid.v4.latte');
        $this['paginator']->setTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'BaseGrid.paginator.v4.latte');
        //
        // data
        //
        $contestants = $this->serviceContestant->getCurrentContestants($presenter->getSelectedContest()->contest_id, $presenter->getSelectedYear());


        $this->setDataSource(new ViewDataSource('ct_id', $contestants));
        $this->setDefaultOrder('name_lex ASC');

        //
        // columns
        //
        $this->addColumn('name', _('Jméno'));
        $this->addColumn('study_year', _('Ročník'));
        $this->addColumn('school_name', _('Škola'));

        //
        // operations
        //
        $this->addButton("editPerson", _("Upravit"))
            ->setText(_('Upravit'))
            ->setLink(function ($row) use ($presenter) {
                return $presenter->link("Contestant:edit", array(
                    'id' => $row->ct_id,
                ));
            });

        $this->addGlobalButton('add')
            ->setLabel('Založit řešitele')
            ->setLink($this->getPresenter()->link('create'));


        //
        // appeareance
        //
        $this->paginate = false;
    }

}

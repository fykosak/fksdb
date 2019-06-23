<?php

namespace FKSDB\Components\Grids;


use FKSDB\ORM\Services\ServiceContestant;
use OrgModule\BasePresenter;
use SQL\ViewDataSource;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ContestantsGrid extends BaseGrid {

    /**
     * @var \FKSDB\ORM\Services\ServiceContestant
     */
    private $serviceContestant;

    /**
     * ContestantsGrid constructor.
     * @param ServiceContestant $serviceContestant
     */
    function __construct(ServiceContestant $serviceContestant) {
        parent::__construct();

        $this->serviceContestant = $serviceContestant;
    }

    /**
     * @param BasePresenter $presenter
     * @throws \Nette\Application\BadRequestException
     * @throws \Nette\Application\UI\InvalidLinkException
     * @throws \NiftyGrid\DuplicateButtonException
     * @throws \NiftyGrid\DuplicateColumnException
     * @throws \NiftyGrid\DuplicateGlobalButtonException
     */
    protected function configure($presenter) {
        parent::configure($presenter);
        //
        // data
        //
        $contestants = $this->serviceContestant->getCurrentContestants($presenter->getSelectedContest()->contest_id, $presenter->getSelectedYear());


        $this->setDataSource(new ViewDataSource('ct_id', $contestants));
        $this->setDefaultOrder('name_lex ASC');

        //
        // columns
        //
        $this->addColumn('name', _('Name'));
        $this->addColumn('study_year', _('Ročník'));
        $this->addColumn('school_name', _('Škola'));

        //
        // operations
        //
        $this->addButton('editPerson', _('Edit'))
            ->setText(_('Edit'))
            ->setLink(function ($row) use ($presenter) {
                return $presenter->link('Contestant:edit', array(
                    'id' => $row->ct_id,
                ));
            });

        $this->addGlobalButton('add')
            ->setLabel(_('Založit řešitele'))
            ->setLink($this->getPresenter()->link('create'));


        //
        // appeareance
        //
        $this->paginate = false;
    }

}

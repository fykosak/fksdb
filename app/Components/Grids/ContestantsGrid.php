<?php

namespace FKSDB\Components\Grids;

use FKSDB\ORM\Models\ModelContestant;
use FKSDB\ORM\Services\ServiceContestant;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Presenter;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;
use NiftyGrid\DuplicateGlobalButtonException;
use SQL\ViewDataSource;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ContestantsGrid extends BaseGrid {

    private ServiceContestant $serviceContestant;

    public function injectServiceContestant(ServiceContestant $serviceContestant): void {
        $this->serviceContestant = $serviceContestant;
    }

    /**
     * @param Presenter|\IContestPresenter $presenter
     * @return void
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     * @throws DuplicateGlobalButtonException
     * @throws InvalidLinkException
     */
    protected function configure(Presenter $presenter): void {
        parent::configure($presenter);
        //
        // data
        //
        $contestants = $this->serviceContestant->getCurrentContestants($presenter->getSelectedContest(), $presenter->getSelectedYear());
        $this->setDataSource(new ViewDataSource('ct_id', $contestants));
        $this->setDefaultOrder('name_lex ASC');

        //
        // columns
        //
        $this->addColumn('name', _('Name'));
        $this->addColumn('study_year', _('Study year'));
        $this->addColumn('school_name', _('School'));

        //
        // operations
        //
        $this->addButton('editPerson', _('Edit'))
            ->setText(_('Edit'))
            ->setLink(function ($row) use ($presenter) {
                /** @var ModelContestant $row */
                return $presenter->link('Contestant:edit', [
                    'id' => $row->ct_id,
                ]);
            });

        $this->addGlobalButton('add')
            ->setLabel(_('Create contestant'))
            ->setLink($this->getPresenter()->link('create'));


        //
        // appeareance
        //
        $this->paginate = false;
    }
}

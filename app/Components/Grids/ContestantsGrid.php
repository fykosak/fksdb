<?php

namespace FKSDB\Components\Grids;

use FKSDB\ORM\Models\ModelContestant;
use FKSDB\ORM\Services\ServiceContestant;
use Nette\Application\BadRequestException;
use Nette\Application\UI\InvalidLinkException;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;
use NiftyGrid\DuplicateGlobalButtonException;
use OrgModule\BasePresenter;
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

    public function injectServiceContestant(ServiceContestant $serviceContestant): void {
        $this->serviceContestant = $serviceContestant;
    }

    /**
     * @param BasePresenter $presenter
     * @throws BadRequestException
     * @throws InvalidLinkException
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     * @throws DuplicateGlobalButtonException
     */
    protected function configure($presenter) {
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

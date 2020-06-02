<?php

namespace FKSDB\Components\Grids;

use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Services\ServiceContestant;
use Nette\Application\BadRequestException;
use Nette\Application\UI\InvalidLinkException;
use Nette\DI\Container;
use NiftyGrid\DataSource\IDataSource;
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
    /**
     * @var int
     */
    private $year;
    /**
     * @var ModelContest
     */
    private $contest;

    /**
     * ContestantsGrid constructor.
     * @param Container $container
     * @param ModelContest $contest
     * @param int $year
     */
    public function __construct(Container $container, ModelContest $contest, int $year) {
        parent::__construct($container);
        $this->contest = $contest;
        $this->year = $year;
    }

    /**
     * @param ServiceContestant $serviceContestant
     * @return void
     */
    public function injectServiceContestant(ServiceContestant $serviceContestant) {
        $this->serviceContestant = $serviceContestant;
    }

    protected function getData(): IDataSource {
        $contestants = $this->serviceContestant->getCurrentContestants($this->contest, $this->year);
        return new ViewDataSource('ct_id', $contestants);
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
                return $presenter->link('Contestant:edit', [
                    'id' => $row->ct_id,
                ]);
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

<?php

namespace FKSDB\Components\Grids;

use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelContestant;
use FKSDB\ORM\Services\ServiceContestant;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;
use NiftyGrid\DuplicateGlobalButtonException;
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
     * @param Presenter $presenter
     * @return void
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     * @throws DuplicateGlobalButtonException
     * @throws InvalidLinkException
     */
    protected function configure(Presenter $presenter) {
        parent::configure($presenter);

        $this->setDefaultOrder('name_lex ASC');

        $this->addColumn('name', _('Name'));
        $this->addColumn('study_year', _('Study year'));
        $this->addColumn('school_name', _('School'));


        $this->addLinkButton('Contestant:edit', 'edit', _('Edit'), false, ['id' => 'ct_id']);
        // $this->addLinkButton('Contestant:detail', 'detail', _('Detail'), false, ['id' => 'ct_id']);

        $this->addGlobalButton('add')
            ->setLabel(_('Založit řešitele'))
            ->setLink($this->getPresenter()->link('create'));

        $this->paginate = false;
    }

    protected function getModelClassName(): string {
        return ModelContestant::class;
    }
}

<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Models\ContestYearModel;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;
use NiftyGrid\DuplicateGlobalButtonException;

class ContestantsGrid extends BaseGrid
{

    private ContestYearModel $contestYear;

    public function __construct(Container $container, ContestYearModel $contestYear)
    {
        parent::__construct($container);
        $this->contestYear = $contestYear;
    }

    protected function getData(): IDataSource
    {
        return new NDataSource($this->contestYear->getContestants());
    }

    /**
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     * @throws DuplicateGlobalButtonException
     * @throws InvalidLinkException
     * @throws BadTypeException
     */
    protected function configure(Presenter $presenter): void
    {
        parent::configure($presenter);

        $this->setDefaultOrder('person.other_name ASC');
        $this->addColumns([
            'person.full_name',
            'contestant.contest_category',
            'person_history.study_year',
        ]);
        $this->addColumn('school_name', _('School'))->setRenderer(
            fn(ContestantModel $row) => $row->getPersonHistory()->school->name_abbrev
        );

        $this->addLinkButton('Contestant:edit', 'edit', _('Edit'), false, ['id' => 'contestant_id']);
        // $this->addLinkButton('Contestant:detail', 'detail', _('Detail'), false, ['id' => 'contestant_id']);

        $this->addGlobalButton('add', _('Create contestant'))
            ->setLink($this->getPresenter()->link('create'));

        $this->paginate = false;
    }
}

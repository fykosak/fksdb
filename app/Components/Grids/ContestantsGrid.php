<?php

declare(strict_types=1);

declare(strict_types=1);

namespace FKSDB\Components\Grids;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\ModelContestant;
use FKSDB\Models\ORM\Models\ModelContestYear;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Presenter;
use Nette\Database\Table\ActiveRow;
use Nette\DI\Container;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;
use NiftyGrid\DuplicateGlobalButtonException;

class ContestantsGrid extends BaseGrid
{

    private ModelContestYear $contestYear;

    public function __construct(Container $container, ModelContestYear $contestYear)
    {
        parent::__construct($container);
        $this->contestYear = $contestYear;
    }

    protected function getData(): IDataSource
    {
        return new NDataSource(
            $this->contestYear->getContest()->related(DbNames::TAB_CONTESTANT_BASE)->where(
                'year',
                $this->contestYear->year
            )
        );
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
            'person_history.study_year',
        ]);
        $this->addColumn('school_name', _('School'))->setRenderer(function (ActiveRow $row) {
            $contestant = ModelContestant::createFromActiveRow($row);
            return $contestant->getPersonHistory()->getSchool()->name_abbrev;
        });

        $this->addLinkButton('Contestant:edit', 'edit', _('Edit'), false, ['id' => 'ct_id']);
        // $this->addLinkButton('Contestant:detail', 'detail', _('Detail'), false, ['id' => 'ct_id']);

        $this->addGlobalButton('add')
            ->setLabel(_('Create contestant'))
            ->setLink($this->getPresenter()->link('create'));

        $this->paginate = false;
    }

    protected function getModelClassName(): string
    {
        return ModelContestant::class;
    }
}

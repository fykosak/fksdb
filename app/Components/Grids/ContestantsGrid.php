<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Models\ContestYearModel;
use Fykosak\Utils\UI\Title;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;

class ContestantsGrid extends BaseGrid
{

    private ContestYearModel $contestYear;

    public function __construct(Container $container, ContestYearModel $contestYear)
    {
        parent::__construct($container);
        $this->contestYear = $contestYear;
    }

    protected function setData(): void
    {
        $this->data = $this->contestYear->getContestants()->order('person.other_name ASC');
    }

    /**
     * @throws InvalidLinkException
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function configure(Presenter $presenter): void
    {
        parent::configure($presenter);
        $this->addColumns([
            'person.full_name',
            'person_history.study_year',
        ]);
        $this->addColumn(
            'school_name',
            new Title(null, _('School')),
            fn(ContestantModel $row) => $this->tableReflectionFactory->loadColumnFactory('school', 'school')->render(
                $row->getPersonHistory(),
                1024
            )
        );

        $this->addPresenterButton('Contestant:edit', 'edit', _('Edit'), false, ['id' => 'contestant_id']);
        // $this->addLinkButton('Contestant:detail', 'detail', _('Detail'), false, ['id' => 'contestant_id']);

        $this->addGlobalButton('add', _('Create contestant'), $this->getPresenter()->link('create'));

        $this->paginate = false;
    }
}

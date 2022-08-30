<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Application;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamState;
use FKSDB\Models\SQL\SearchableDataSource;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Presenter;
use Nette\Database\Table\Selection;
use Nette\DI\Container;
use Nette\Forms\Form;
use Nette\Utils\Html;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;
use NiftyGrid\DuplicateGlobalButtonException;

class TeamApplicationsGrid extends BaseGrid
{
    protected EventModel $event;

    public function __construct(EventModel $event, Container $container)
    {
        parent::__construct($container);
        $this->event = $event;
    }

    /**
     * @throws BadTypeException
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     * @throws DuplicateGlobalButtonException
     * @throws InvalidLinkException
     */
    protected function configure(Presenter $presenter): void
    {

        $this->paginate = false;

        $this->addColumns([
            'fyziklani_team.fyziklani_team_id',
            'fyziklani_team.name',
            'fyziklani_team.state',
            'fyziklani_team.game_lang',
            'fyziklani_team.category',
            'fyziklani_team.force_a',
            'fyziklani_team.phone',
        ]);
        $this->addLinkButton('detail', 'detail', _('Detail'), false, ['id' => 'fyziklani_team_id']);
        $this->addCSVDownloadButton();
        parent::configure($presenter);
    }

    protected function getData(): IDataSource
    {
        $participants = $this->event->getFyziklaniTeams();
        $source = new SearchableDataSource($participants);
        $source->setFilterCallback($this->getFilterCallBack());
        return $source;
    }

    public function getFilterCallBack(): callable
    {
        return function (Selection $table, array $value): void {
            $states = [];
            foreach ($value['status'] as $state => $value) {
                if ($value) {
                    $states[] = str_replace('__', '.', $state);
                }
            }
            if (count($states)) {
                $table->where('status IN ?', $states);
            }
        };
    }

    /**
     * @throws BadTypeException
     * @throws NotImplementedException
     */
    protected function createComponentSearchForm(): FormControl
    {
        $control = new FormControl($this->getContext());
        $form = $control->getForm();
        $stateContainer = new ContainerWithOptions($this->getContext());
        $stateContainer->setOption('label', _('States'));
        foreach (TeamState::cases() as $state) {
            $label = Html::el('span')
                ->addHtml(Html::el('b')->addText($state->label()))
                ->addText(': ');
            $stateContainer->addCheckbox(str_replace('.', '__', $state->value), $label);
        }
        $form->addComponent($stateContainer, 'state');
        $form->addSubmit('submit', _('Apply filter'));
        $form->onSuccess[] = function (Form $form): void {
            $values = $form->getValues('array');
            $this->searchTerm = $values;
            $this->dataSource->applyFilter($values);
            $count = $this->dataSource->getCount();
            $this->getPaginator()->itemCount = $count;
        };
        return $control;
    }
}

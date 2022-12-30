<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Application;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Components\Grids\FilterBaseGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamState;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;
use Nette\Forms\Form;
use Nette\Utils\Html;

class TeamApplicationsGrid extends FilterBaseGrid
{
    protected EventModel $event;

    public function __construct(EventModel $event, Container $container)
    {
        parent::__construct($container);
        $this->event = $event;
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function configure(Presenter $presenter): void
    {
        $this->data = $this->event->getTeams();
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
        $this->addPresenterButton('detail', 'detail', _('Detail'), false, ['id' => 'fyziklani_team_id']);
        //$this->addCSVDownloadButton();
    }

    protected function getFilterCallBack(): void
    {
        $states = [];
        foreach ($this->searchTerm['state'] as $state => $value) {
            if ($value) {
                $states[] = str_replace('__', '.', $state);
            }
        }
        if (count($states)) {
            $this->data->where('state IN ?', $states);
        }
    }

    /**
     * @throws BadTypeException
     * @throws NotImplementedException
     */
    protected function createComponentSearchForm(): FormControl
    {
        $control = new FormControl($this->container);
        $form = $control->getForm();
        $stateContainer = new ContainerWithOptions($this->container);
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
            $this->searchTerm = $form->getValues('array');
        };
        return $control;
    }
}

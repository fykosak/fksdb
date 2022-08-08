<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Application;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\Fyziklani\TeamState;
use Fykosak\NetteORM\TypedGroupedSelection;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Presenter;
use Nette\Forms\Form;
use Nette\Utils\Html;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;
use NiftyGrid\DuplicateGlobalButtonException;

class TeamApplicationsGrid extends AbstractApplicationsGrid
{

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
        ]);
        $this->addLinkButton('detail', 'detail', _('Detail'), false, ['id' => 'fyziklani_team_id']);
        $this->addCSVDownloadButton();
        parent::configure($presenter);
    }

    protected function getSource(): TypedGroupedSelection
    {
        return $this->event->getFyziklaniTeams();
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

    protected function getHoldersColumns(): array
    {
        return [
            'note',
            'game_lang',
            'category',
            'force_a',
            'phone',
            'password',
        ];
    }

    protected function getTableName(): string
    {
        return DbNames::TAB_FYZIKLANI_TEAM;
    }
}

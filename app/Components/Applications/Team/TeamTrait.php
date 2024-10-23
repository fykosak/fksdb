<?php

declare(strict_types=1);

namespace FKSDB\Components\Applications\Team;

use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\GameLang;
use FKSDB\Models\ORM\Models\Fyziklani\TeamCategory;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\Fyziklani\TeamState;
use Fykosak\NetteORM\Selection\TypedGroupedSelection;
use Nette\Forms\Form;

trait TeamTrait
{
    private EventModel $event;

    /**
     * @phpstan-return TypedGroupedSelection<TeamModel2>
     */
    protected function getModels(): TypedGroupedSelection
    {
        $query = $this->event->getTeams();
        /** @var string $key */
        foreach ($this->filterParams as $key => $filterParam) {
            if (!$filterParam) {
                continue;
            }
            switch ($key) {
                case 'category':
                    $query->where('category', $filterParam);
                    break;
                case 'game_lang':
                    $query->where('game_lang', $filterParam);
                    break;
                case 'name':
                    $query->where('name LIKE ?', '%' . $filterParam . '%');
                    break;
                case 'state':
                    $query->where('state', $filterParam);
                    break;
                case 'team_id':
                    $query->where('fyziklani_team_id', $filterParam);
            }
        }
        return $query;
    }

    protected function configureForm(Form $form): void
    {
        $form->addText('name', _('Team name'))->setOption(
            'description',
            _('Works as %name%, characters "%" will be added automatically.')
        );
        $form->addText('team_id', _('Team Id'))->setHtmlType('number');
        $categories = [];
        foreach (TeamCategory::casesForEvent($this->event) as $teamCategory) {
            $categories[$teamCategory->value] = $teamCategory->label();
        }
        $form->addSelect('category', _('Category'), $categories)->setPrompt(_('Select category'));

        $gameLang = [];
        foreach (GameLang::cases() as $lang) {
            $gameLang[$lang->value] = $lang->label();
        }
        $form->addSelect('game_lang', _('Game language'), $gameLang)->setPrompt(_('Select language'));

        $states = [];
        foreach (TeamState::cases() as $teamState) {
            $states[$teamState->value] = $teamState->label();
        }
        $form->addSelect('state', _('State'), $states)->setPrompt(_('Select state'));
    }
}

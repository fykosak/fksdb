<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Tests\Event;

use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Tests\Adapter;
use Fykosak\NetteORM\Model\Model;
use Nette\Utils\Html;

/**
 * @phpstan-extends Adapter<EventModel,TeamModel2>
 */
class TeamAdapter extends Adapter
{
    protected function getModels(Model $model): iterable
    {
        return $model->getTeams(); // @phpstan-ignore-line
    }

    protected function getLogPrepend(Model $model): Html
    {
        return Html::el()
            ->addText(_('In team '))
            ->addHtml(
                Html::el('a')
                    ->addAttributes(
                        [
                            'href' => $this->linkGenerator->link(
                                'Event:Team:detail',
                                ['id' => $model->fyziklani_team_id, 'eventId' => $model->event_id]
                            ),
                        ]
                    )
                    ->addText($model->name)
            );
    }

    public function getId(): string
    {
        return 'Team' . $this->test->getId();
    }
}

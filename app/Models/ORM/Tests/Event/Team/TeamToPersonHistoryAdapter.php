<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Tests\Event\Team;

use FKSDB\Models\ORM\Tests\Adapter;
use FKSDB\Models\ORM\Models\Fyziklani\TeamMemberModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\PersonHistoryModel;
use Fykosak\NetteORM\Model\Model;
use Nette\Utils\Html;

/**
 * @phpstan-extends Adapter<TeamModel2,PersonHistoryModel>
 */
class TeamToPersonHistoryAdapter extends Adapter
{
    protected function getModels(Model $model): iterable
    {
        $models = [];
        $members = $model->getMembers();
        /** @var TeamMemberModel $member */
        foreach ($members as $member) {
            $history = $member->getPersonHistory();
            if ($history) {
                $models[] = $history;
            }
        }
        return $models;
    }

    protected function getLogPrepend(Model $model): Html
    {
        return Html::el()
            ->addText(_('In person '))
            ->addHtml(
                Html::el('a')
                    ->addAttributes(
                        ['href' => $this->linkGenerator->link('Organizer:Person:detail', ['id' => $model->person_id])]
                    )
                    ->addText($model->person->getFullName())
            )
            ->addHtml(' in related year');
    }

    public function getId(): string
    {
        return 'PersonHistory' . $this->test->getId();
    }
}

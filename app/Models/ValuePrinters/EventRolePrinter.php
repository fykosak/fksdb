<?php

declare(strict_types=1);

namespace FKSDB\Models\ValuePrinters;

use FKSDB\Models\Authorization\EventRole\{ContestOrgRole,
    EventOrgRole,
    EventRole,
    FyziklaniTeamTeacherRole,
    FyziklaniTeamMemberRole,
    ParticipantRole
};
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\PersonModel;
use Nette\SmartObject;
use Nette\Utils\Html;

class EventRolePrinter
{
    use SmartObject;

    /**
     * @throws NotImplementedException
     */
    public function __invoke(PersonModel $person, EventModel $event): Html
    {
        $container = Html::el('span');
        $roles = $person->getEventRoles($event);
        if (!count($roles)) {
            $container->addHtml(
                Html::el('span')
                    ->addAttributes(['class' => 'badge bg-danger'])
                    ->addText(_('No role'))
            );
            return $container;
        }
        return $this->getHtml($roles);
    }

    /**
     * @param EventRole[] $roles
     * @throws NotImplementedException
     */
    private function getHtml(array $roles): Html
    {
        $container = Html::el('span');

        foreach ($roles as $role) {
            if ($role instanceof FyziklaniTeamTeacherRole) {
                foreach ($role->teams as $team) {
                    $container->addHtml(
                        Html::el('span')
                            ->addAttributes(['class' => 'badge bg-color-9'])
                            ->addText(_('Teacher') . ' - ' . $team->name)
                    );
                }
            } elseif ($role instanceof EventOrgRole) {
                $container->addHtml(
                    Html::el('span')
                        ->addAttributes(['class' => 'badge bg-color-7'])
                        ->addText(_('Event org') . ($role->eventOrg->note ? (' - ' . $role->eventOrg->note) : ''))
                );
            } elseif ($role instanceof FyziklaniTeamMemberRole) {
                $container->addHtml(
                    Html::el('span')
                        ->addAttributes(['class' => 'badge bg-color-9'])
                        ->addText(
                            _('Team member') . ' - ' . _($role->member->fyziklani_team->state->label())
                        )
                );
            } elseif ($role instanceof ParticipantRole) {
                $container->addHtml(
                    Html::el('span')
                        ->addAttributes(['class' => 'badge bg-color-10'])
                        ->addText(
                            _('Participant') . ' - ' . _($role->eventParticipant->status)
                        )
                );
            } elseif ($role instanceof ContestOrgRole) {
                $container->addHtml(
                    Html::el('span')
                        ->addAttributes(['class' => 'badge bg-color-6'])
                        ->addText(_('Contest org'))
                );
            }
        }
        return $container;
    }
}

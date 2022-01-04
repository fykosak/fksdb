<?php

declare(strict_types=1);

namespace FKSDB\Models\ValuePrinters;

use FKSDB\Models\Authorization\EventRole\{
    ContestOrgRole,
    EventOrgRole,
    EventRole,
    FyziklaniTeacherRole,
    ParticipantRole,
};
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Models\ModelPerson;
use Nette\SmartObject;
use Nette\Utils\Html;

class EventRolePrinter
{
    use SmartObject;

    public function __invoke(ModelPerson $person, ModelEvent $event): Html
    {
        $container = Html::el('span');
        $roles = $person->getEventRoles($event);
        if (!count($roles)) {
            $container->addHtml(
                Html::el('span')
                    ->addAttributes(['class' => 'badge badge-danger'])
                    ->addText(_('No role'))
            );
            return $container;
        }
        return $this->getHtml($roles);
    }

    /**
     * @param EventRole[] $roles
     * @return Html
     */
    private function getHtml(array $roles): Html
    {
        $container = Html::el('span');

        foreach ($roles as $role) {
            if ($role instanceof FyziklaniTeacherRole) {
                foreach ($role->teams as $team) {
                    $container->addHtml(
                        Html::el('span')
                            ->addAttributes(['class' => 'badge badge-9'])
                            ->addText(_('Teacher') . ' - ' . $team->name)
                    );
                }
            } elseif ($role instanceof EventOrgRole) {
                $container->addHtml(
                    Html::el('span')
                        ->addAttributes(['class' => 'badge badge-7'])
                        ->addText(_('Event org') . ($role->eventOrg->note ? (' - ' . $role->eventOrg->note) : ''))
                );
            } elseif ($role instanceof ParticipantRole) {
                $team = $role->eventParticipant->getFyziklaniTeam();
                $container->addHtml(
                    Html::el('span')
                        ->addAttributes(['class' => 'badge badge-10'])
                        ->addText(
                            _('Participant') . ' - ' . _($role->eventParticipant->status) .
                            ($team ? (' - team: ' . $team->name) : '')
                        )
                );
            } elseif ($role instanceof ContestOrgRole) {
                $container->addHtml(
                    Html::el('span')
                        ->addAttributes(['class' => 'badge badge-6'])
                        ->addText(_('Contest org'))
                );
            }
        }
        return $container;
    }
}

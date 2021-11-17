<?php

declare(strict_types=1);

namespace FKSDB\Models\ValuePrinters;

use FKSDB\Models\Events\EventRole\ContestOrgRole;
use FKSDB\Models\Events\EventRole\EventOrgRole;
use FKSDB\Models\Events\EventRole\EventRoles;
use FKSDB\Models\Events\EventRole\FyziklaniTeacherRole;
use FKSDB\Models\Events\EventRole\ParticipantRole;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Models\ModelPerson;
use Nette\Application\BadRequestException;
use Nette\SmartObject;
use Nette\Utils\Html;

class EventRolePrinter
{
    use SmartObject;

    public function __invoke(ModelPerson $person, ModelEvent $event): Html
    {
        $container = Html::el('span');
        $roles = $person->getEventRoles($event);
        if (!$roles->hasRoles()) {
            $container->addHtml(
                Html::el('span')
                    ->addAttributes(['class' => 'badge badge-danger'])
                    ->addText(_('No role'))
            );
            return $container;
        }
        return $this->getHtml($roles);
    }

    private function getHtml(EventRoles $roles): Html
    {
        $container = Html::el('span');

        foreach ($roles->getRoles() as $role) {
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
                $team = null;
                try {
                    $team = $role->eventParticipant->getFyziklaniTeam();
                } catch (BadRequestException $exception) {
                }
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

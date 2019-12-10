<?php

namespace FKSDB\Components\Events;

use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelEventParticipant;
use FKSDB\ORM\Models\ModelPerson;
use Nette\Application\BadRequestException;
use Nette\Utils\Html;

/**
 * Class EventRole
 * @package FKSDB\Components\Events
 */
class EventRole {
    /**
     * @param ModelPerson $person
     * @param ModelEvent $event
     * @return Html
     */
    public static function getHtml(ModelPerson $person, ModelEvent $event): Html {
        $container = Html::el('span');
        $roles = $person->getRolesForEvent($event);
        if (!\count($roles)) {
            $container->addHtml(Html::el('span')
                ->addAttributes(['class' => 'badge badge-danger'])
                ->addText(_('No role')));
            return $container;
        }
        foreach ($roles as $role) {
            switch ($role['type']) {
                case 'teacher':
                    $container->addHtml(Html::el('span')
                        ->addAttributes(['class' => 'badge badge-1'])
                        ->addText(_('Teacher') . ' - ' . $role['team']->name));
                    break;
                case'eventOrg':
                    $container->addHtml(Html::el('span')
                        ->addAttributes(['class' => 'badge badge-5'])
                        ->addText(_('Event org') . ' - ' . $role['eventOrg']->note));
                    break;
                case'participant':
                    $team = null;
                    /**
                     * @var ModelEventParticipant $participant
                     */
                    $participant = $role['participant'];
                    try {
                        $team = $participant->getFyziklaniTeam();
                    } catch (BadRequestException $exception) {
                    }
                    $container->addHtml(Html::el('span')
                        ->addAttributes(['class' => 'badge badge-3'])
                        ->addText(_('Participant') . ' - ' . _($participant->status) .
                            ($team ? (' - team: ' . $team->name) : '')
                        ));
                    break;
                case 'contestOrg':
                    $container->addHtml(Html::el('span')
                        ->addAttributes(['class' => 'badge badge-9'])
                        ->addText(_('Contest org')));
            }
        }
        return $container;
    }
}

<?php

namespace FKSDB\Components\DatabaseReflection\ValuePrinters;

use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelEventParticipant;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\YearCalculator;
use Nette\Application\BadRequestException;
use Nette\Utils\Html;

/**
 * Class EventRole
 * *
 */
class EventRole {

    public static function calculateRoles(ModelPerson $person, ModelEvent $event, YearCalculator $yearCalculator): Html {
        $container = Html::el('span');
        $roles = $person->getRolesForEvent($event, $yearCalculator);
        if (!\count($roles)) {
            $container->addHtml(Html::el('span')
                ->addAttributes(['class' => 'badge badge-danger'])
                ->addText(_('No role')));
            return $container;
        }
        return self::getHtml($roles);
    }

    public static function getHtml(array $roles): Html {
        $container = Html::el('span');

        foreach ($roles as $role) {
            switch ($role['type']) {
                case 'teacher':
                    $container->addHtml(Html::el('span')
                        ->addAttributes(['class' => 'badge badge-9'])
                        ->addText(_('Teacher') . ' - ' . $role['team']->name));
                    break;
                case'org':
                    $container->addHtml(Html::el('span')
                        ->addAttributes(['class' => 'badge badge-7'])
                        ->addText(_('Event org') . ($role['org']->note ? (' - ' . $role['org']->note) : '')));
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
                        ->addAttributes(['class' => 'badge badge-10'])
                        ->addText(_('Participant') . ' - ' . _($participant->status) .
                            ($team ? (' - team: ' . $team->name) : '')
                        ));
                    break;
                case 'contest_org':
                    $container->addHtml(Html::el('span')
                        ->addAttributes(['class' => 'badge badge-6'])
                        ->addText(_('Contest org')));
            }
        }
        return $container;
    }
}

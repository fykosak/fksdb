<?php

namespace FKSDB\Components\DatabaseReflection\ValuePrinters;

use FKSDB\ORM\Models\ModelEventParticipant;
use FKSDB\ORM\Models\Schedule\ModelPersonSchedule;
use Nette\Application\BadRequestException;
use Nette\Utils\Html;

/**
 * Class EventRole
 * @package FKSDB\Components\DatabaseReflection\ValuePrinters
 */
class EventRole {
    /**
     * @param array $roles
     * @return Html
     */
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

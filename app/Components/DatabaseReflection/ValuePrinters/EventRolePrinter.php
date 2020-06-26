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
 * @author Michal Červeňák <miso@fykos.cz>
 */
class EventRolePrinter {
    /**
     * @var YearCalculator
     */
    private $yearCalculator;

    /**
     * EventRolePrinter constructor.
     * @param YearCalculator $yearCalculator
     */
    public function __construct(YearCalculator $yearCalculator) {
        $this->yearCalculator = $yearCalculator;
    }

    public function __invoke(ModelPerson $person, ModelEvent $event): Html {
        if (!$person) {
            Html::el('span')
                ->addAttributes(['class' => 'badge badge-danger'])
                ->addText(_('No user found'));
        }
        $container = Html::el('span');
        $roles = $person->getRolesForEvent($event, $this->yearCalculator);
        if (!\count($roles)) {
            $container->addHtml(Html::el('span')
                ->addAttributes(['class' => 'badge badge-danger'])
                ->addText(_('No role')));
            return $container;
        }
        return $this->getHtml($roles);
    }

    private function getHtml(array $roles): Html {
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

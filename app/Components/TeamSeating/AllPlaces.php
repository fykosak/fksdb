<?php

declare(strict_types=1);

namespace FKSDB\Components\TeamSeating;

use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Nette\DI\Container;

class AllPlaces extends BaseComponent
{
    private EventModel $event;

    public function __construct(Container $container, EventModel $event)
    {
        parent::__construct($container);
        $this->event = $event;
    }

    final public function render(string $dev): void
    {
        $places = [];
        /** @var TeamModel2 $team */
        foreach ($this->event->getTeams() as $team) {
            $place = $team->getPlace();
            if ($place) {
                $places[$place->label()] = $team;
            }
        }

        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . './@layout.latte', [
            'lang' => $this->translator->lang,
            'places' => $this->template->places = Place2024::getAll(),
            'teams' => $this->template->teams = $places,
            'dev' => $dev,
        ]);
    }
}

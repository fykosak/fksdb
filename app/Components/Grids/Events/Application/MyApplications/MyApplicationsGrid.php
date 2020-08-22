<?php

namespace FKSDB\Components\Grids\Events\Application;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\ORM\Models\ModelEventParticipant;
use FKSDB\ORM\Models\ModelPerson;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;
use NiftyGrid\DuplicateButtonException;

/**
 * Class MyApplicationsGrid
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class MyApplicationsGrid extends BaseGrid {

    protected ModelPerson $person;

    /**
     * MyApplicationsGrid constructor.
     * @param ModelPerson $person
     * @param Container $container
     */
    public function __construct(ModelPerson $person, Container $container) {
        parent::__construct($container);
        $this->person = $person;
    }

    /**
     * @param Presenter $presenter
     * @return void
     * @throws DuplicateButtonException
     */
    protected function configure(Presenter $presenter): void {
        parent::configure($presenter);

        $this->paginate = false;
        $this->addButton('edit')->setText(_('Edit'))->setLink(function ($row): string {
            $model = ModelEventParticipant::createFromActiveRow($row);
            return $this->getPresenter()->link(':Public:Application:edit', $model->getEvent()->isTeamEvent() ? [
                'eventId' => $model->event_id,
                'id' => $model->getFyziklaniTeam()->e_fyziklani_team_id,
            ] : [
                'eventId' => $model->event_id,
                'id' => $model->event_participant_id,
            ]);
        });
    }
}

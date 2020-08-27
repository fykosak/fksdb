<?php

namespace FKSDB\Components\Grids;

use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelEventOrg;
use FKSDB\ORM\Models\ModelPerson;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;

/**
 * Class EventOrgsGrid
 * @author Michal Červeňák <miso@fykos.cz>
 */
class TaskContributionsGrid extends BaseGrid {

    private ModelPerson $person;

    /**
     * EventOrgsGrid constructor.
     * @param ModelPerson $person
     * @param Container $container
     */
    public function __construct(ModelPerson $person, Container $container) {
        parent::__construct($container);
        $this->person = $person;
    }

    protected function getData(): IDataSource {
        return new NDataSource($this->person->ref(DbNames::TAB_TASK_CONTRIBUTION, 'person_id'));
    }

    /**
     * @param Presenter $presenter
     * @return void
     * @throws BadTypeException
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     */
    protected function configure(Presenter $presenter): void {
        parent::configure($presenter);
        $this->paginate = false;
        $this->addColumns(['event.event', 'contest.contest', 'event_org.note']);
        $this->addLinkButton(':Event:EventOrg:edit', 'edit', _('Edit'), false, ['eventId' => 'event_id', 'id' => 'e_org_id']);
        $this->addLinkButton(':Event:EventOrg:detail', 'detail', _('Detail'), false, ['eventId' => 'event_id', 'id' => 'e_org_id']);
    }

    protected function getModelClassName(): string {
        return ModelEventOrg::class;
    }
}

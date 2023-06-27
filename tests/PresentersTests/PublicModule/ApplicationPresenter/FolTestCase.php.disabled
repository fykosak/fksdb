<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\PublicModule\ApplicationPresenter;

use FKSDB\Models\ORM\Models\Fyziklani\TeamModel;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\Fyziklani\TeamService;
use FKSDB\Tests\Events\EventTestCase;
use Nette\Application\IPresenter;
use Nette\Utils\DateTime;
use Tester\Assert;

abstract class FolTestCase extends EventTestCase
{

    protected IPresenter $fixture;
    protected PersonModel $person;
    protected EventModel $event;

    protected function getEvent(): EventModel
    {
        return $this->event;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $future = DateTime::from(time() + DateTime::DAY);
        $this->event = $this->createEvent([
            'event_type_id' => 9,
            'event_year' => 4,
            'begin' => $future,
            'end' => $future,
            'parameters' => <<<EOT
EOT
            ,
        ]);

        $this->fixture = $this->createPresenter('Public:Application');
        $this->mockApplication();

        $this->person = $this->createPerson(
            'Paní',
            'Bílá',
            ['email' => 'bila@hrad.cz', 'born' => DateTime::from('2000-01-01')],
            []
        );
    }

    protected function assertTeamApplication(EventModel $event, string $teamName): TeamModel
    {
        $application = $this->getContainer()
            ->getByType(TeamService::class)
            ->getTable()
            ->where(['event_id' => $event->event_id, 'name' => $teamName])
            ->fetch();
        Assert::notEqual(null, $application);
        return $application;
    }
}

<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\PublicModule\ApplicationPresenter;

use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Models\ModelPerson;
use FKSDB\Models\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use FKSDB\Tests\Events\EventTestCase;
use Nette\Application\IPresenter;
use Nette\Utils\DateTime;
use Tester\Assert;

abstract class FolTestCase extends EventTestCase
{

    protected IPresenter $fixture;
    protected ModelPerson $person;
    protected ModelEvent $event;

    protected function getEvent(): ModelEvent
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

    protected function assertTeamApplication(ModelEvent $event, string $teamName): ModelFyziklaniTeam
    {
        $application = $this->getContainer()
            ->getByType(ServiceFyziklaniTeam::class)
            ->getTable()
            ->where(['event_id' => $event->event_id, 'name' => $teamName])
            ->fetch();
        Assert::notEqual(null, $application);
        return $application;
    }
}

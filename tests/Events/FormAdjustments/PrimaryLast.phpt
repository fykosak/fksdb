<?php

declare(strict_types=1);

namespace FKSDB\Tests\Events\FormAdjustments;

use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Services\Events\ServiceDsefParticipant;
use FKSDB\Models\ORM\Services\ServiceEventParticipant;
use Nette\Application\Request;
use Nette\Application\Responses\TextResponse;
use Nette\Application\UI\Template;
use Nette\Utils\DateTime;
use Tester\Assert;
use Tester\DomQuery;

$container = require '../../Bootstrap.php';

class PrimaryLast extends ResourceAvailabilityTestCase
{

    private EventParticipantModel $app;

    protected function setUp(): void
    {
        parent::setUp();

        $person = $this->createPerson(
            'Paní',
            'Černá',
            ['email' => 'cerna@hrad.cz', 'born' => DateTime::from('2000-01-01')],
            []
        );
        $this->app = $this->getContainer()->getByType(ServiceEventParticipant::class)->createNewModel(
            [
                'person_id' => $person->person_id,
                'event_id' => $this->event->event_id,
                'status' => 'applied',
                'accomodation' => 1,
            ]
        );
        $this->getContainer()->getByType(ServiceDsefParticipant::class)->createNewModel([
            'event_participant_id' => $this->app->event_participant_id,
            'e_dsef_group_id' => 1,
        ]);
        $this->authenticatePerson($person, $this->fixture);
    }

    public function testDisplay(): void
    {
        $request = new Request('Public:Application', 'GET', [
            'action' => 'default',
            'lang' => 'cs',
            'contestId' => (string)1,
            'year' => (string)1,
            'eventId' => (string)$this->event->event_id,
            'id' => (string)$this->app->event_participant_id,
        ]);

        $response = $this->fixture->run($request);
        Assert::type(TextResponse::class, $response);
        /** @var TextResponse $response */
        $source = $response->getSource();
        Assert::type(Template::class, $source);

        $html = (string)$source;
        $dom = DomQuery::fromHtml($html);
        Assert::true((bool)$dom->xpath('//input[@name="participant[accomodation]"]'));
        Assert::false((bool)$dom->xpath('//input[@name="participant[accomodation]"][@disabled="disabled"]'));
    }

    protected function getCapacity(): int
    {
        return 3;
    }
}

$testCase = new PrimaryLast($container);
$testCase->run();

<?php

declare(strict_types=1);

namespace FKSDB\Tests\Events\FormAdjustments;

use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Services\EventService;
use FKSDB\Models\ORM\Services\EventParticipantService;
use Nette\Application\Request;
use Nette\Application\Responses\TextResponse;
use Nette\Application\UI\Template;
use Tester\Assert;
use Tester\DomQuery;

$container = require '../../Bootstrap.php';

class SecondaryLimitOk extends ResourceAvailabilityTestCase
{

    private EventModel $tsafEvent;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tsafEvent = $this->createEvent([
            'event_type_id' => 7,
            'event_year' => 7,
            'parameters' => <<<EOT
EOT
            ,
        ]);

        foreach ($this->persons as $person) {
            $this->getContainer()->getByType(EventParticipantService::class)->createNewModel([
                'person_id' => $person->person_id,
                'event_id' => $this->tsafEvent->event_id,
                'status' => 'applied',
                'accomodation' => 1,
            ]);
        }
    }

    public function getTestData(): array
    {
        return [
            [3, false],
            [2, true],
        ];
    }

    /**
     * @dataProvider getTestData
     */
    public function testDisplay(int $capacity, bool $disabled): void
    {
        Assert::equal(
            2,
            (int)$this->getContainer()
                ->getByType(EventParticipantService::class)
                ->getTable()
                ->where(['event_id' => $this->event->event_id])
                ->sum('accomodation')
        );
        $this->getContainer()->getByType(EventService::class)->updateModel($this->event, [
            'parameters' => <<<EOT
accomodationCapacity: $capacity                
EOT]);
        $request = new Request('Public:Application', 'GET', [
            'action' => 'default',
            'lang' => 'cs',
            'contestId' => (string)1,
            'year' => (string)1,
            'eventId' => (string)$this->tsafEvent->event_id,
        ]);
        $response = $this->fixture->run($request);

        Assert::type(TextResponse::class, $response);
        /** @var TextResponse $response */
        $source = $response->getSource();
        Assert::type(Template::class, $source);

        $html = (string)$source;
        $dom = DomQuery::fromHtml($html);
        Assert::true((bool)$dom->xpath('//input[@name="participantDsef[accomodation]"]'));
        Assert::equal(
            $disabled,
            (bool)$dom->xpath('//input[@name="participantDsef[accomodation]"][@disabled="disabled"]')
        );
    }

    protected function getCapacity(): int
    {
        return 3;
    }
}

$testCase = new SecondaryLimitOk($container);
$testCase->run();

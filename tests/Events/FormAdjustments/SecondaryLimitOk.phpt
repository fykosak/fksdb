<?php

namespace FKSDB\Tests\Events\FormAdjustment;

use FKSDB\Tests\Events\FormAdjustments\ResourceAvailabilityTestCase;
use Nette\Application\Request;
use Nette\Application\Responses\TextResponse;
use Nette\Application\UI\ITemplate;
use Tester\Assert;
use Tester\DomQuery;

$container = require '../../bootstrap.php';

class SecondaryLimitOk extends ResourceAvailabilityTestCase {
    /**
     * @var int
     */
    private $tsafEventId;

    protected function setUp() {
        parent::setUp();
        $this->tsafEventId = $this->createEvent([
            'event_type_id' => 7,
            'event_year' => 7,
            'parameters' => <<<EOT
EOT
            ,
        ]);

        foreach ($this->persons as $personId) {
            $eid = $this->insert('event_participant', [
                'person_id' => $personId,
                'event_id' => $this->tsafEventId,
                'status' => 'applied',
                'accomodation' => 1,
            ]);
            $this->insert('e_tsaf_participant', [
                'event_participant_id' => $eid,
            ]);
        }
    }

    protected function tearDown() {
        $this->connection->query('DELETE FROM e_tsaf_participant');
        parent::tearDown();
    }

    public function getTestData(): array {
        return [
            [3, false],
            [2, true],
        ];
    }

    /**
     * @dataProvider getTestData
     */
    public function testDisplay(int $capacity, bool $disabled) {
        Assert::equal(2, (int)$this->connection->query('SELECT SUM(accomodation) FROM event_participant WHERE event_id = ?', $this->eventId)->fetchField());
        $this->connection->query('UPDATE event SET parameters = ? WHERE event_id = ?', <<<EOT
accomodationCapacity: $capacity                
EOT
            , $this->eventId);
        $request = new Request('Public:Application', 'GET', [
            'action' => 'default',
            'lang' => 'cs',
            'contestId' => 1,
            'year' => 1,
            'eventId' => $this->tsafEventId,
        ]);
        $response = $this->fixture->run($request);

        Assert::type(TextResponse::class, $response);

        $source = $response->getSource();
        Assert::type(ITemplate::class, $source);

        $html = (string)$source;
        $dom = DomQuery::fromHtml($html);
        Assert::true((bool)$dom->xpath('//input[@name="participantDsef[accomodation]"]'));
        Assert::equal($disabled, (bool)$dom->xpath('//input[@name="participantDsef[accomodation]"][@disabled="disabled"]'));
    }

    protected function getCapacity(): int {
        return 3;
    }
}

$testCase = new SecondaryLimitOk($container);
$testCase->run();

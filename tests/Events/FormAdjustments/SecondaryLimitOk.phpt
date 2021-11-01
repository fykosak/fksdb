<?php

declare(strict_types=1);

namespace FKSDB\Tests\Events\FormAdjustments;

use Nette\Application\Request;
use Nette\Application\Responses\TextResponse;
use Nette\Application\UI\Template;
use Tester\Assert;
use Tester\DomQuery;

$container = require '../../Bootstrap.php';

class SecondaryLimitOk extends ResourceAvailabilityTestCase
{

    private int $tsafEventId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tsafEventId = $this->createEvent([
            'event_type_id' => 7,
            'event_year' => 7,
            'parameters' => <<<EOT
EOT
            ,
        ]);

        foreach ($this->persons as $personId) {
            $this->insert('event_participant', [
                'person_id' => $personId,
                'event_id' => $this->tsafEventId,
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
            (int)$this->explorer->query(
                'SELECT SUM(accomodation) FROM event_participant WHERE event_id = ?',
                $this->eventId
            )->fetchField()
        );
        $this->explorer->query(
            'UPDATE event SET parameters = ? WHERE event_id = ?',
            <<<EOT
accomodationCapacity: $capacity                
EOT
            ,
            $this->eventId
        );
        $request = new Request('Public:Application', 'GET', [
            'action' => 'default',
            'lang' => 'cs',
            'contestId' => (string)1,
            'year' => (string)1,
            'eventId' => (string)$this->tsafEventId,
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

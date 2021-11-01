<?php

declare(strict_types=1);

namespace FKSDB\Tests\Events\FormAdjustments;

use Nette\Application\Request;
use Nette\Application\Responses\TextResponse;
use Nette\Application\UI\Template;
use Nette\Utils\DateTime;
use Tester\Assert;
use Tester\DomQuery;

$container = require '../../Bootstrap.php';

class PrimaryLast extends ResourceAvailabilityTestCase
{

    private int $appId;

    protected function setUp(): void
    {
        parent::setUp();

        $personId = $this->createPerson(
            'Paní',
            'Černá',
            ['email' => 'cerna@hrad.cz', 'born' => DateTime::from('2000-01-01')],
            []
        );
        $this->appId = $this->insert('event_participant', [
            'person_id' => $personId,
            'event_id' => $this->eventId,
            'status' => 'applied',
            'accomodation' => 1,
        ]);
        $this->insert('e_dsef_participant', [
            'event_participant_id' => $this->appId,
            'e_dsef_group_id' => 1,
        ]);
        $this->authenticate($personId, $this->fixture);
    }

    public function testDisplay(): void
    {
        $request = new Request('Public:Application', 'GET', [
            'action' => 'default',
            'lang' => 'cs',
            'contestId' => (string)1,
            'year' => (string)1,
            'eventId' => (string)$this->eventId,
            'id' => (string)$this->appId,
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

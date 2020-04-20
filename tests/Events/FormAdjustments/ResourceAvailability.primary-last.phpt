<?php

namespace Events\Model;

use Nette\Application\Request;
use Nette\Application\Responses\TextResponse;
use Nette\Templating\ITemplate;
use Nette\Utils\DateTime;
use Tester\Assert;
use Tester\DomQuery;

$container = require '../../bootstrap.php';

class ResourceAvailabilityTest extends ResourceAvailabilityTestCase {

    private $appId;

    protected function setUp() {
        parent::setUp();

        $personId = $this->createPerson('Paní', 'Černá', ['email' => 'cerna@hrad.cz', 'born' => DateTime::from('2000-01-01')], true);
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
        $this->authenticate($personId);
    }

    public function testDisplay() {
        $request = new Request('Public:Application', 'GET', [
            'action' => 'default',
            'lang' => 'cs',
            'contestId' => 1,
            'year' => 1,
            'eventId' => $this->eventId,
            'id' => $this->appId,
        ]);

        $response = $this->fixture->run($request);
        Assert::type(TextResponse::class, $response);

        $source = $response->getSource();
        Assert::type(ITemplate::class, $source);

        $html = (string)$source;
        $dom = DomQuery::fromHtml($html);
        Assert::true((bool)$dom->xpath('//input[@name="participant[accomodation]"]'));
        Assert::false((bool)$dom->xpath('//input[@name="participant[accomodation]"][@disabled="disabled"]'));
    }

    public function getCapacity() {
        return 3;
    }

}

$testCase = new ResourceAvailabilityTest($container);
$testCase->run();

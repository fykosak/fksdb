<?php

namespace Events\Model;

use Nette\Application\Request;
use Nette\DateTime;
use Tester\Assert;
use Tester\DomQuery;

$container = require '../../bootstrap.php';

class ResourceAvailabilityTest extends ResourceAvailabilityTestCase {

    private $appId;

    protected function setUp() {
        parent::setUp();

        $personId = $this->createPerson('Paní', 'Černá', array('email' => 'cerna@hrad.cz', 'born' => DateTime::from('2000-01-01')), true);
        $this->appId = $this->insert('event_participant', array(
            'person_id' => $personId,
            'event_id' => $this->eventId,
            'status' => 'applied',
            'accomodation' => 1,
        ));
        $this->insert('e_dsef_participant', array(
            'event_participant_id' => $this->appId,
        ));
        $this->authenticate($personId);
    }

    public function testDisplay() {
        $request = new Request('Public:Application', 'GET', array(
            'action' => 'default',
            'lang' => 'cs',
            'contestId' => 1,
            'year' => 1,
            'eventId' => $this->eventId,
            'id' => $this->appId,
        ));

        $response = $this->fixture->run($request);
        Assert::type('Nette\Application\Responses\TextResponse', $response);

        $source = $response->getSource();
        Assert::type('Nette\Templating\ITemplate', $source);

        $html = (string) $source;
        $dom = DomQuery::fromHtml($html);
        Assert::true((bool) $dom->xpath('//input[@name="participant[accomodation]"]'));
        Assert::false((bool) $dom->xpath('//input[@name="participant[accomodation]"][@disabled="disabled"]'));
    }

    public function getCapacity() {
        return 3;
    }

}

$testCase = new ResourceAvailabilityTest($container);
$testCase->run();

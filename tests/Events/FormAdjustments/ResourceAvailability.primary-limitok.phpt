<?php

namespace Events\Model;

use Nette\Application\Request;
use Tester\Assert;
use Tester\DomQuery;

$container = require '../../bootstrap.php';

class ResourceAvailabilityTest extends ResourceAvailabilityTestCase {

    public function testDisplay() {
        $request = new Request('Public:Application', 'GET', array(
            'action' => 'default',
            'lang' => 'cs',
            'contestId' => 1,
            'year' => 1,
            'eventId' => $this->eventId,
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

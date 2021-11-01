<?php

declare(strict_types=1);

namespace FKSDB\Tests\Events\FormAdjustments;

use Nette\Application\Request;
use Nette\Application\Responses\TextResponse;
use Nette\Application\UI\Template;
use Tester\Assert;
use Tester\DomQuery;

$container = require '../../Bootstrap.php';

class PrimaryLimitOk extends ResourceAvailabilityTestCase
{

    public function testDisplay(): void
    {
        $request = new Request('Public:Application', 'GET', [
            'action' => 'default',
            'lang' => 'cs',
            'contestId' => (string)1,
            'year' => (string)1,
            'eventId' => (string)$this->eventId,
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

$testCase = new PrimaryLimitOk($container);
$testCase->run();

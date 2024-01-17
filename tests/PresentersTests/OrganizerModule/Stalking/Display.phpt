<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\OrganizerModule\Stalking;

// phpcs:disable
$container = require '../../../Bootstrap.php';

// phpcs:enable
use FKSDB\Models\Authorization\ContestRole;
use Nette\Application\Responses\TextResponse;
use Nette\Application\UI\Template;
use Tester\Assert;

class Display extends StalkingTestCase
{

    public function testDisplay(): void
    {
        $request = $this->createRequest();

        $response = $this->fixture->run($request);
        Assert::type(TextResponse::class, $response);
        /** @var TextResponse $response */
        $source = $response->getSource();
        Assert::type(Template::class, $source);
        $html = (string)$source;

        Assert::contains('Base info', $html); // contains headline
        Assert::contains('class="fas fa-mars"', $html); // check gender
        Assert::contains('flag-icon-cz', $html); // phone number flag
        Assert::contains('flag-icon-sk', $html); // phone number flag
        Assert::contains('+420 123 456 789', $html); // check phone number formating
        Assert::contains('href="mailto:tester&#64;example.com"', $html); // email
        Assert::contains('(111) Všeobecná zdravotní pojišťovna ČR', $html); // Health insurance mapping
        Assert::contains('data-frontend-id="chart.person.detail.timeline"', $html); // timeline working?
    }

    protected function getUserRoleId(): string
    {
        return ContestRole::Cartesian;
    }
}
// phpcs:disable
$testCase = new Display($container);
$testCase->run();
// phpcs:enable

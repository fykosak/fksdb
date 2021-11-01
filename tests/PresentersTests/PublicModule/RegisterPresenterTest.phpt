<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\PublicModule;

$container = require '../../Bootstrap.php';

use FKSDB\Tests\ModelsTests\DatabaseTestCase;
use Nette\Application\IPresenter;
use Nette\Application\IPresenterFactory;
use Nette\Application\Request;
use Nette\Application\Responses\TextResponse;
use Nette\Bridges\ApplicationLatte\Template;
use Tester\Assert;

class RegisterPresenterTest extends DatabaseTestCase
{

    private IPresenter $fixture;

    protected function setUp(): void
    {
        parent::setUp();

        $presenterFactory = $this->getContainer()->getByType(IPresenterFactory::class);
        $this->fixture = $presenterFactory->createPresenter('Public:Register');
        $this->fixture->autoCanonicalize = false;
    }

    public function testDispatch(): void
    {
        $request = new Request('Public:Register', 'GET', [
            'action' => 'contest',
            'lang' => 'en',
        ]);

        $response = $this->fixture->run($request);
        Assert::type(TextResponse::class, $response);
        /** @var TextResponse $response */
        $source = $response->getSource();
        Assert::type(Template::class, $source);

        $html = (string)$source;
        Assert::contains('Select contest', $html);
    }

    public function testForm()
    {
        $request = new Request('Public:Register', 'GET', [
            'action' => 'contestant',
            'contestId' => 1,
            'year' => 1,
            'lang' => 'en',
        ]);

        $response = $this->fixture->run($request);
        Assert::type(TextResponse::class, $response);
        /** @var TextResponse $response */
        $source = $response->getSource();
        Assert::type(Template::class, $source);

        $html = $source->__toString();
        Assert::contains('contestant application', $html);
    }
}

$testCase = new RegisterPresenterTest($container);
$testCase->run();

<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\PublicModule;

// phpcs:disable
$container = require '../../Bootstrap.php';

// phpcs:enable
use FKSDB\Modules\CoreModule\RegisterPresenter;
use FKSDB\Tests\ModelsTests\DatabaseTestCase;
use Nette\Application\IPresenterFactory;
use Nette\Application\Request;
use Nette\Application\Responses\TextResponse;
use Nette\Bridges\ApplicationLatte\Template;
use Tester\Assert;

class RegisterPresenterTest extends DatabaseTestCase
{
    private RegisterPresenter $fixture;

    protected function setUp(): void
    {
        parent::setUp();

        $presenterFactory = $this->container->getByType(IPresenterFactory::class);
        /** @var RegisterPresenter $presenter */
        $presenter = $presenterFactory->createPresenter('Core:Register');
        $this->fixture = $presenter;
        $this->fixture->autoCanonicalize = false;
    }

    public function testDispatch(): void
    {
        $request = new Request('Core:Register', 'GET', [
            'action' => 'default',
            'lang' => 'en',
        ]);

        $response = $this->fixture->run($request);
        Assert::type(TextResponse::class, $response);
        /** @var TextResponse $response */
        $source = $response->getSource();
        Assert::type(Template::class, $source);

        $html = (string)$source;
        Assert::contains('Register', $html);
    }

    public function testForm(): void
    {
        $request = new Request('Core:Register', 'GET', [
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
// phpcs:disable
$testCase = new RegisterPresenterTest($container);
$testCase->run();
// phpcs:enable

<?php

$container = require '../bootstrap.php';

use Events\EventTestCase;
use Nette\Application\Request;
use Nette\DI\Container;
use PublicModule\RegisterPresenter;
use Tester\Assert;

class ApplicationPresenterTest extends EventTestCase {

    /**
     * @var Container
     */
    private $container;

    /**
     * @var RegisterPresenter
     */
    private $fixture;

    function __construct(Container $container) {
        parent::__construct($container->getService('nette.database.default'));
        $this->container = $container;
    }

    protected function setUp() {
        parent::setUp();

        $this->eventId = $this->createEvent(array(
            'event_type_id' => 2,
            'event_year' => 20,
            'parameters' => <<<EOT
groups:
    Alpha: 2
    Bravo: 2
EOT
        ));

        $presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
        $this->fixture = $presenterFactory->createPresenter('Public:Application');
        $this->fixture->autoCanonicalize = false;

        $this->container->getByType('Authentication\LoginUserStorage')->setPresenter($this->fixture);

        $this->mockApplication($this->container);
    }

    protected function tearDown() {
        $this->connection->query("DELETE FROM e_dsef_participant");
        parent::tearDown();
    }

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
        Assert::contains('Účastník', $html);
    }

    public function testAnonymousRegistration() {
        $request = $this->createPostRequest(array(
            'participant' => array(
                'person_id' => "__promise",
                'person_id_1' => array(
                    '_c_compact' => " ",
                    'person' => array(
                        'other_name' => "František",
                        'family_name' => "Dobrota",
                    ),
                    'person_info' => array(
                        'email' => "ksaad@kalo.cz",
                        'id_number' => "1231354",
                        'born' => "15. 09. 2014",
                    ),
                    'post_contact' => array(
                        'address' => array(
                            'target' => "jkljhkjh",
                            'city' => "jkhlkjh",
                            'postal_code' => "64546",
                            'country_iso' => "",
                        ),
                    ),
                ),
                'e_dsef_group_id' => "1",
                'lunch_count' => "3",
                'message' => "",
            ),
            'privacy' => "on",
            'c_a_p_t_cha' => "pqrt",
            '__init__applied' => "Přihlásit účastníka",
        ));

        $response = $this->fixture->run($request);
        Assert::type('Nette\Application\Responses\RedirectResponse', $response);

        $application = $this->assertApplication($this->eventId, 'ksaad@kalo.cz');
        Assert::equal('applied', $application->status);

        $eApplication = $this->assertExtendedApplication($application, 'e_dsef_participant');
        Assert::equal(1, $eApplication->e_dsef_group_id);
        Assert::equal(3, $eApplication->lunch_count);
    }

}

$testCase = new ApplicationPresenterTest($container);
$testCase->run();

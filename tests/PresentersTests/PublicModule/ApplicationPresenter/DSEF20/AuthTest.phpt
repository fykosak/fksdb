<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\PublicModule\ApplicationPresenter\DSEF20;

$container = require '../../../../Bootstrap.php';

use FKSDB\Tests\PresentersTests\PublicModule\ApplicationPresenter\DsefTestCase;
use Nette\Application\Request;
use Nette\Application\Responses\RedirectResponse;
use Nette\Application\Responses\TextResponse;
use Nette\Application\UI\Template;
use Nette\Utils\DateTime;
use Tester\Assert;

class AuthTest extends DsefTestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        $this->authenticate($this->personId, $this->fixture);
    }

    public function testDisplay(): void
    {
        Assert::equal(true, $this->fixture->getUser()->isLoggedIn());

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
        Assert::contains('Účastník', $html);

        Assert::contains('Paní Bílá', $html);
    }

    public function testAuthRegistration(): void
    {
        Assert::equal(true, $this->fixture->getUser()->isLoggedIn());

        $request = $this->createPostRequest([
            'participant' => [
                'person_id' => (string)$this->personId,
                'person_id_1' => [
                    '_c_compact' => " ",
                    'person' => [
                        'other_name' => "Paní",
                        'family_name' => "Bílá",
                    ],
                    'person_info' => [
                        'email' => "bila@hrad.cz",
                        'id_number' => "1231354",
                        'born' => "2014-09-15",
                    ],
                    'post_contact_p' => [
                        'address' => [
                            'target' => "jkljhkjh",
                            'city' => "jkhlkjh",
                            'postal_code' => "64546",
                            'country_iso' => "",
                        ],
                    ],
                ],
                'e_dsef_group_id' => "1",
                'lunch_count' => "3",
                'message' => "",
            ],
            'privacy' => "on",
            'c_a_p_t_cha' => "pqrt",
            '__init__applied' => "Přihlásit účastníka",
        ]);

        $response = $this->fixture->run($request);
        Assert::type(RedirectResponse::class, $response);

        $application = $this->assertApplication($this->eventId, 'bila@hrad.cz');
        Assert::equal('applied', $application->status);
        Assert::equal((int)$this->personId, $application->person_id);

        $info = $this->assertPersonInfo($this->personId);
        Assert::equal('1231354', $info->id_number);
        Assert::equal(DateTime::from('2014-09-15'), $info->born);

        $eApplication = $this->assertExtendedApplication($application, 'e_dsef_participant');
        Assert::equal(1, $eApplication->e_dsef_group_id);
        Assert::equal(3, $application->lunch_count);
    }
}

$testCase = new AuthTest($container);
$testCase->run();

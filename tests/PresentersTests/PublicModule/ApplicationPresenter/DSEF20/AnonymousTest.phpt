<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\PublicModule\ApplicationPresenter\DSEF20;

// phpcs:disable
$container = require '../../../../Bootstrap.php';

// phpcs:enable
use FKSDB\Tests\PresentersTests\PublicModule\ApplicationPresenter\DsefTestCase;
use Nette\Application\Request;
use Nette\Application\Responses\RedirectResponse;
use Nette\Application\Responses\TextResponse;
use Nette\Application\UI\Template;
use Tester\Assert;

class AnonymousTest extends DsefTestCase
{

    public function testDisplay(): void
    {
        $request = new Request('Public:Application', 'GET', [
            'action' => 'default',
            'lang' => 'cs',
            'contestId' => 1,
            'year' => 1,
            'eventId' => $this->event->event_id,
        ]);

        $response = $this->fixture->run($request);
        Assert::type(TextResponse::class, $response);
        /** @var TextResponse $response */
        $source = $response->getSource();
        Assert::type(Template::class, $source);

        $html = (string)$source;
        Assert::contains('Účastník', $html);
    }

    public function testAnonymousRegistration(): void
    {
        $request = $this->createPostRequest([
            'participant' => [
                'person_id' => "__promise",
                'person_id_container' => [
                    '_c_compact' => " ",
                    'person' => [
                        'other_name' => "František",
                        'family_name' => "Dobrota",
                    ],
                    'person_info' => [
                        'email' => "ksaad@kalo.cz",
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

        $application = $this->assertApplication($this->event, 'ksaad@kalo.cz');
        Assert::equal('applied', $application->status);

        $eApplication = $this->assertExtendedApplication($application, 'e_dsef_participant');
        Assert::equal(1, $eApplication->e_dsef_group_id);
        Assert::equal(3, $application->lunch_count);
    }
}
// phpcs:disable
$testCase = new AnonymousTest($container);
$testCase->run();
// phpcs:enable

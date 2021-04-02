<?php

namespace FKSDB\Models\Authentication\Provider;

use League\OAuth2\Client\Provider\Google;
use Nette\Application\LinkGenerator;
use Nette\Application\UI\InvalidLinkException;
use Nette\SmartObject;

class GoogleProvider extends Google {
    use SmartObject;

    /**
     * GoogleProvider constructor.
     * @param string $clientId
     * @param string $clientSecret
     * @param LinkGenerator $linkGenerator
     * @throws InvalidLinkException
     */
    public function __construct(string $clientId, string $clientSecret, LinkGenerator $linkGenerator) {
        parent::__construct([
            'clientId' => $clientId,
            'clientSecret' => $clientSecret,
            'scope' => 'openid email',
            'redirectUri' => $linkGenerator->link('Core:Authentication:google', ['bc' => null]),
        ]);
    }
}

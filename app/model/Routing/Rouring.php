<?php

namespace FKSDB;

use Nette\Application\IRouter;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;

class Routing extends \Nette\Object {
    # Map=>contest_id => contest indentifier
    const contestMapping = [
        1 => 'fykos',
        2 => 'vyfuk',
    ];

    # Map=>contest identifier => contest_id
    const inverseContestMapping = [
        'fykos' => 1,
        'vyfuk' => 2,
    ];

    /**
     * @return IRouter
     */
    public static function createRouter() {

        $router = new RouteList();
        # PHP-like scripts compatibility
        $router[] = new Route('index.php',
            [
                'module' => 'Public',
                'presenter' => 'Dashboard',
                'action' => 'default',
            ], [
                Route::ONE_WAY,
                Route::SECURED,
            ]
        );
        # Backward compatibility
        $router[] = new Route('web-service/<action>',
            [
                'module' => 'Org',
                'presenter' => 'WebService',
                'action' => 'default',
            ], [
                Route::ONE_WAY,
                Route::SECURED,
            ]
        );
        # Cool URL
        $router[] = new Route('%path%<contestId %contests%><year [0-9]+>[.<series [0-9]+>]/q/<qid>',
            [
                'subdomain' => '%subdomain.db%',
                'tld' => '%domain.tld%',
                'module' => 'Org',
                'presenter' => 'Export',
                'action' => 'execute',
                'contestId' => ['filterTable' => self::inverseContestMapping],
            ],
            Route::SECURED);
        # Central authentication domain (+ logout must be enabled at each domain too)
        $router[] = new Route('//[!<subdomain>].<domainHost>.[!<tld>]%path%<action logout>',
            [
                'presenter' => 'Authentication',
                'subdomain' => '%subdomain.db%',
                'tld' => '%domain.tld%',
            ], Route::SECURED);

        $router[] = new Route('//<subdomain>.%domain.cz%%path%<action login|logout|fb-login|recover>',
            [
                'presenter' => 'Authentication',
                'subdomain' => '%subdomain.auth%',
                'tld' => '%domain.tld%',
            ],
            Route::SECURED);
        # Registration must be at the same domain as central authentication.
        $router[] = new Route('//[!<subdomain>].%domain.cz%%path%<presenter register>/[<contestId %contests%>/[year<year [0-9]+>/[person<personId -?[0-9]+>/]]]<action=default>',
            [
                'module' => 'Public',
                'subdomain' => '%subdomain.auth%',
                'tld' => '%domain.tld%',
                'contestId' => ['filterTable' => self::inverseContestMapping],
                'year' => null,
            ],
            Route::SECURED);
        $router[] = new Route('//<subdomain>.<domainHost>.[!<tld>]%path%[<contestId %contests%>/]<presenter register>/<action=default>',
            [
                'module' => 'Public',
                'subdomain' => '%subdomain.auth%',
                'tld' => '%domain.tld%',
                'contestId' => ['filterTable' => self::inverseContestMapping],
            ], [
                Route::ONE_WAY,
                Route::SECURED,
            ]
        );
        $router[] = new Route('//[!<subdomain>].<domainHost>.[!<tld>]%path%',
            [
                'subdomain' => '%subdomain.db%',
                'presenter' => 'Dispatch',
                'tld' => '%domain.tld%',
                'action' => 'default',
            ],
            Route::SECURED);
        # Application itself (note the 'presenter's w/out 'module' are handled specially)
        $router[] = new Route('//[!<subdomain>].<domainHost>.[!<tld>]%path%[<contestId %contests%>[<year [0-9]+>]/]<presenter %rootpresenters%>/<action=default>[/<id>]',
            [
                'subdomain' => '%subdomain.db%',
                'tld' => '%domain.tld %',
                'contestId' => ['filterTable' => self::inverseContestMapping],
            ],
            Route::SECURED);
        $router[] = new Route('//[!<subdomain>].<domainHost>.[!<tld>]%path%fyziklani[<eventID [0-9]+>]/<presenter>/<action=default>[/<id>]',
            [
                'subdomain' => '%subdomain.db%',
                'tld' => '%domain.tld%',
                'module' => 'Fyziklani',
            ],
            Route::SECURED);
        $router[] = new Route('//[!<subdomain>].<domainHost>.[!<tld>]%path%f[<eventID [0-9]+>]/s/q[/<id>]',
            [
                'subdomain' => '%subdomain.db%',
                'presenter' => 'Submit',
                'action' => 'qrEntry',
                'tld' => '%domain.tld%',
                'module' => 'Fyziklani',
            ],
            Route::SECURED);
        $router[] = new Route('//[!<subdomain>].<domainHost>.[!<tld>]%path%[<contestId %contests%>[<year [0-9]+>]/]<module %modules%>/<presenter>/<action=default>[/<id>]',
            [
                'presenter' => 'Dashboard',
                'subdomain' => '%subdomain.db%',
                'tld' => '%domain.tld%',
                'contestId' => ['filterTable' => self::inverseContestMapping],
            ],
            Route::SECURED);

        return $router;
    }
}

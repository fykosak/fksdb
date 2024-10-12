<?php

declare(strict_types=1);

namespace FKSDB\Models\Authentication;

use FKSDB\Models\ORM\Models\LoginModel;
use FKSDB\Models\ORM\Services\LoginService;
use Nette\Utils\DateTime;
use Tracy\Debugger;

/**
 * @note IAuthenticator interface is not explicitly implemented due to 'array'
 * type hint at authenticate method.
 */
abstract class AbstractAuthenticator /* implements IAuthenticator */
{

}

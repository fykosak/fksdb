<?php

namespace Github\Events;

use Github\Repository;
use Github\User;
use Nette\Object;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
abstract class Event extends Object {

	const HTTP_HEADER = 'X-GitHub-Event';

	/** @var Repository $repository */
	public $repository;

	/** @var User $user */
	public $sender;

}

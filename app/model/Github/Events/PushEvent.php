<?php

namespace Github\Events;

use Github\Events\Event;


/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class PushEvent extends Event {

	const REFS_HEADS = 'refs/heads/';

	/** @var string */
	public $ref;

	/** @var string */
	public $after;

	/** @var string */
	public $before;

}

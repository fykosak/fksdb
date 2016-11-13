<?php

namespace Github\Events;

use Github\Events\Event;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class PingEvent extends Event {

	/** @var string */
	public $zen;

	/** @var string */
	public $hook_id;
}


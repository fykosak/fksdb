<?php

namespace FKSDB\Models\Github\Events;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class PingEvent extends Event {

	public string $zen;

	public string $hook_id;
}

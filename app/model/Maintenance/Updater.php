<?php

namespace Maintenance;

use FKS\Config\GlobalParameters;
use Nette\Diagnostics\Debugger;
use Nette\Object;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class Updater extends Object {

	/** @var GlobalParameters */
	private $globalParameters;

	function __construct(GlobalParameters $globalParameters) {
		$this->globalParameters = $globalParameters;
	}

	public function installBranch($requestedBranch) {
		$deployment = $this->globalParameters['updater']['deployment'];
		foreach ($deployment as $path => $branch) {
			if ($branch != $requestedBranch) {
				continue;
			}
			$this->install($path, $branch);
		}
	}

	private function install($path, $branch) {
		$user = $this->globalParameters['updater']['installUser'];
		$script = $this->globalParameters['updater']['installScript'];
		$cmd = "sudo -u {$user} {$script} $path $branch >/dev/null 2>/dev/null &";
		Debugger::log("Running: $cmd");
		shell_exec($cmd);
	}

}

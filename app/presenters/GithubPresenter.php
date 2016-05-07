<?php

use Github\EventFactory;
use Github\Events\Event;
use Nette\Application\Responses\TextResponse;
use Nette\Diagnostics\Debugger;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class GithubPresenter extends AuthenticatedPresenter {

	/** @var $parsedEvent Event */
	private $parsedEvent;

	/** @var EventFactory */
	private $eventFactory;

	public function injectEventFactory(EventFactory $eventFactory) {
		$this->eventFactory = $eventFactory;
	}

	private function getEvent() {
		if ($this->parsedEvent === null) {
			$this->parsedEvent = Event::createFromRequest($request);
		}
	}

	public function getAllowedAuthMethods() {
		return AuthenticatedPresenter::AUTH_ALLOW_GITHUB;
	}

	public function authorizedApi() {
		//TODO check authorization
		$this->setAuthorized(true);
	}

	public function actionApi() {
		$type = $this->getFullHttpRequest()->getRequest()->getHeader(Event::HTTP_HEADER);
		$payload = $this->getFullHttpRequest()->getPayload();
		$data = json_decode($payload, true);
		Debugger::log(var_export($data, true));

		$event = $this->eventFactory->createEvent($type, $data);
		Debugger::log(var_export($event, true));
	}

	public function renderApi() {
		$response = new TextResponse("DONE");
		$this->sendResponse($response);
	}

}

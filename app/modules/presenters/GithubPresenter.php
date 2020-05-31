<?php

use Github\EventFactory;
use Github\Events\Event;
use Github\Events\PushEvent;
use Maintenance\Updater;
use Nette\Application\AbortException;
use Nette\Application\Responses\TextResponse;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class GithubPresenter extends AuthenticatedPresenter {

    private Updater $updater;

    private EventFactory $eventFactory;

    public function injectEventFactory(EventFactory $eventFactory): void {
        $this->eventFactory = $eventFactory;
    }

    public function injectUpdater(Updater $updater): void {
        $this->updater = $updater;
    }

    /**
     * @return bool|int|string
     */
    public function getAllowedAuthMethods() {
        return AuthenticatedPresenter::AUTH_ALLOW_GITHUB;
    }

    public function authorizedApi(): void {
        /* Already authenticated user has ultimate access to this presenter. */
        $this->setAuthorized(true);
    }

    public function actionApi(): void {
        $type = $this->getFullHttpRequest()->getRequest()->getHeader(Event::HTTP_HEADER);
        $payload = $this->getFullHttpRequest()->getPayload();
        $data = json_decode($payload, true);

        $event = $this->eventFactory->createEvent($type, $data);
        if ($event instanceof PushEvent) {
            if (strncasecmp(PushEvent::REFS_HEADS, $event->ref, strlen(PushEvent::REFS_HEADS))) {
                return;
            }
            $branch = substr($event->ref, strlen(PushEvent::REFS_HEADS));
            $this->updater->installBranch($branch);
        }
    }

    /**
     * @throws AbortException
     */
    public function renderApi(): void {
        $response = new TextResponse("Thank you, Github.");
        $this->sendResponse($response);
    }

}

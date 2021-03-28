<?php

namespace FKSDB\Modules\CoreModule;

use FKSDB\Modules\Core\AuthenticatedPresenter;
use FKSDB\Models\Github\EventFactory;
use FKSDB\Models\Github\Events\Event;
use FKSDB\Models\Github\Events\PushEvent;
use FKSDB\Models\Maintenance\Updater;
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

    final public function injectQuarterly(EventFactory $eventFactory, Updater $updater): void {
        $this->eventFactory = $eventFactory;
        $this->updater = $updater;
    }

    public function getAllowedAuthMethods(): int {
        return AuthenticatedPresenter::AUTH_ALLOW_GITHUB;
    }

    public function authorizedApi(): void {
        /* Already authenticated user has ultimate access to this presenter. */
        $this->setAuthorized(true);
    }

    public function actionApi(): void {
        $type = $this->getHttpRequest()->getHeader(Event::HTTP_HEADER);
        $payload = $this->getHttpRequest()->getRawBody();
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
    final public function renderApi(): void {
        $response = new TextResponse('Thank you, Github.');
        $this->sendResponse($response);
    }
}

<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Transition;

use FKSDB\Models\Events\Model\ApplicationHandlerException;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Transition\UnavailableTransitionsException;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Fykosak\Utils\Logging\Message;
use Nette\Application\ForbiddenRequestException;
use Nette\DI\Container;

class TransitionButtonsComponent extends BaseComponent
{
    use TransitionComponent;

    private ModelHolder $holder;

    public function __construct(Container $container, EventModel $event, ModelHolder $holder)
    {
        parent::__construct($container);
        $this->holder = $holder;
        $this->event = $event;
    }

    /**
     * @throws BadTypeException
     */
    final public function render(): void
    {
        $this->template->transitions = $this->getMachine()->getAvailableTransitions($this->holder);
        $this->template->holder = $this->holder;
        /** @phpstan-ignore-next-line */
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'buttons.latte');
    }

    /**
     * @throws \Throwable
     */
    public function handleTransition(string $transitionName): void
    {
        try {
            $transition = $this->getMachine()->getTransitionById($transitionName);
            $this->getMachine()->execute($transition, $this->holder);
            $this->getPresenter()->flashMessage(_('Transition successful'), Message::LVL_SUCCESS);
        } catch (ApplicationHandlerException | ForbiddenRequestException | UnavailableTransitionsException $exception) {
            $this->getPresenter()->flashMessage($exception->getMessage(), Message::LVL_ERROR);
        } catch (\Throwable$exception) {
            $this->getPresenter()->flashMessage(_('Some error emerged'), Message::LVL_ERROR);
        }
        $this->getPresenter()->redirect('this');
    }
}

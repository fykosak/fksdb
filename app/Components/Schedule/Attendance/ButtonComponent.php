<?php

declare(strict_types=1);

namespace FKSDB\Components\Schedule\Attendance;

use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use FKSDB\Models\ORM\Services\Schedule\PersonScheduleService;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Fykosak\Utils\Logging\Message;
use Nette\DI\Container;

class ButtonComponent extends BaseComponent
{
    private PersonScheduleModel $model;
    private PersonScheduleService $service;

    public function __construct(Container $container, PersonScheduleModel $model)
    {
        parent::__construct($container);
        $this->model = $model;
    }

    public function inject(PersonScheduleService $service): void
    {
        $this->service = $service;
    }

    public function render(): void
    {
        $this->template->model = $this->model;
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.latte');
    }

    public function handleAttendance(): void
    {
        try {
            $this->service->makeAttendance($this->model);
        } catch (\Throwable $exception) {
            $this->flashMessage(_('error: ') . $exception->getMessage(), Message::LVL_ERROR);
            $this->getPresenter()->redirect('this');
        }
        $this->flashMessage(
            sprintf(_('Transition successful for %s'), $this->model->person->getFullName()),
            Message::LVL_SUCCESS
        );
        $this->getPresenter()->redirect('this');
    }
}

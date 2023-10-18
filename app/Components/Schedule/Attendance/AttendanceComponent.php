<?php

declare(strict_types=1);

namespace FKSDB\Components\Schedule\Attendance;

use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use FKSDB\Models\ORM\Services\Schedule\PersonScheduleService;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Fykosak\Utils\Logging\Message;
use Nette\Application\BadRequestException;
use Nette\DI\Container;

class AttendanceComponent extends BaseComponent
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

    /**
     * @throws NotFoundException
     * @throws BadRequestException
     */
    public function handleAttendance(): void
    {
        if ($this->model->state) {
            $this->getPresenter()->flashMessage(_('Transition is not available'), Message::LVL_ERROR);
            $this->getPresenter()->redirect('this');
        }
        $this->model->checkPayment();
        $this->service->makeAttendance($this->model);
        $this->getPresenter()->flashMessage(_('Transition successful'), Message::LVL_SUCCESS);
        $this->getPresenter()->redirect('this');
    }
}

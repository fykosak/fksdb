<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids;

use FKSDB\Components\Grids\Components\BaseGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\TeacherModel;
use FKSDB\Models\ORM\Services\TeacherService;
use Fykosak\NetteORM\TypedSelection;

class TeachersGrid extends BaseGrid
{
    private TeacherService $teacherService;

    public function inject(TeacherService $teacherService): void
    {
        $this->teacherService = $teacherService;
    }

    /**
     * @phpstan-return TypedSelection<TeacherModel>
     */
    protected function getModels(): TypedSelection
    {
        return $this->teacherService->getTable();
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function configure(): void
    {
        $this->addColumns([
            'person.full_name',
            'teacher.note',
            'school.school',
            'teacher.role',
            'teacher.active',
        ]);
        $this->addORMLink('teacher.edit');
        $this->addORMLink('teacher.detail');
    }
}

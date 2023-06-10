<?php

declare(strict_types=1);

use FKSDB\Components\Game\Submits\TaskCodePreprocessor;
use FKSDB\Models\ORM\Models\Fyziklani\TaskModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Services\Fyziklani\TaskService;
use FKSDB\Models\ORM\Services\Fyziklani\TeamService2;
use Nette\DI\Container;

const SAFE_LIMIT = 500;

/** @var Container $container */
$container = require __DIR__ . '/bootstrap.php';

$teamService = $container->getByType(TeamService2::class);
$teams = $teamService->getTable()->where('event_id', 175);
$taskService = $container->getByType(TaskService::class);
$tasks = $taskService->getTable()->where('event_id', 175);

echo '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>TEAM codes</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
    td,th{
    border: 1px solid #ccc; 
    }
    
    table{
    border-collapse: collapse;
    }
</style>
</head>
<body>';

echo '<table><thead><tr><th></th>';
/** @var TaskModel $task */
foreach ($tasks as $task) {
    echo '<th>' . $task->name . '(' . $task->label . ')</th>';
}
echo '</tr></thead>';
echo '<tbody>';
/** @var TeamModel2 $team */
foreach ($teams as $team) {
    echo '<tr><td>' . $team->name . '(' . $team->fyziklani_team_id . ')</td>';
    /** @var TaskModel $task */
    foreach ($tasks as $task) {
        $numCode = '00' . TaskCodePreprocessor::getNumLabel($team->fyziklani_team_id . $task->label);
        $subCode = str_split(TaskCodePreprocessor::getNumLabel($numCode));
        $sum = (7 * ($subCode[0] + $subCode[3] + $subCode[6])
                + 3 * ($subCode[1] + $subCode[4] + $subCode[7])
                + 9 * ($subCode[2] + $subCode[5])) % 10;
        echo '<td>00' . $team->fyziklani_team_id . $task->label . $sum . '</td>';
    }
    echo '</tr>';
}
echo '</tbody></table>';
echo '</body></html>';

import { TaskModel } from 'FKSDB/Models/ORM/Models/Fyziklani/task-model';
import { TeamModel } from 'FKSDB/Models/ORM/Models/Fyziklani/team-model';
import * as React from 'react';
import { useContext } from 'react';
import { useSelector } from 'react-redux';
import Row from './row';
import { Store } from 'FKSDB/Components/Game/ResultsAndStatistics/reducers/store';
import { TranslatorContext } from '@translator/context';

export default function Index() {
    const translator = useContext(TranslatorContext);
    const filter = useSelector((state: Store) => state.tableFilter.filter);
    const submits = useSelector((state: Store) => state.data.submits);
    const tasks = useSelector((state: Store) => state.data.tasks);
    const teams = useSelector((state: Store) => state.data.teams);
    const submitsForTeams = {};
    for (const index in submits) {
        if (Object.hasOwn(submits, index)) {
            const submit = submits[index];
            const {teamId, taskId: taskId} = submit;
            submitsForTeams[teamId] = submitsForTeams[teamId] || {};
            submitsForTeams[teamId][taskId] = submit;
        }
    }
    return <div className="mb-3 game-statistics-table">
        <h1>{filter ? filter.getHeadline() : translator.getText('Results')}</h1>
        <table className="table-striped table-hover table table-sm bg-white">
            <thead>
            <tr>
                <th/>
                <th/>
                <th>∑</th>
                <th>∑</th>
                <th>x̄</th>
                {tasks.map((task: TaskModel, taskIndex) =>
                    <th key={taskIndex} data-task-label={task.label}>{task.label}</th>)}
            </tr>
            </thead>
            <tbody>
            {teams.map((team: TeamModel, teamIndex) =>
                <Row
                    tasks={tasks}
                    submits={submitsForTeams[team.teamId] || {}}
                    team={team}
                    key={teamIndex}
                    visible={(filter ? filter.match(team) : true)}
                />,
            )}
            </tbody>
        </table>
    </div>;
}

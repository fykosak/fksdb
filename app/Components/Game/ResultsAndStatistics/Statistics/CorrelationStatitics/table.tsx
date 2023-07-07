import { SubmitModel } from 'FKSDB/Models/ORM/Models/Fyziklani/submit-model';
import { TaskModel } from 'FKSDB/Models/ORM/Models/Fyziklani/task-model';
import * as React from 'react';
import { useContext } from 'react';
import { useSelector } from 'react-redux';
import { getTimeLabel } from '../Middleware/correlation';
import { getAverageNStandardDeviation } from '../Middleware/std-dev';
import { calculateSubmitsForTeams } from '../Middleware/submits-for-teams';
import { Store } from 'FKSDB/Components/Game/ResultsAndStatistics/reducers/store';
import { TranslatorContext } from '@translator/context';

export default function Table() {
    const translator = useContext(TranslatorContext);
    const firstTeamId = useSelector((state: Store) => state.statistics.firstTeamId);
    const secondTeamId = useSelector((state: Store) => state.statistics.secondTeamId);
    const submits = useSelector((state: Store) => state.data.submits);
    const tasks = useSelector((state: Store) => state.data.tasks);

    const firstTeamSubmits: SubmitModel[] = [];
    const secondTeamSubmits: SubmitModel[] = [];
    for (const id in submits) {
        if (Object.hasOwn(submits, id)) {
            const submit = submits[id];
            if (submit.teamId === firstTeamId) {
                firstTeamSubmits.push(submit);
            } else if (submit.teamId === secondTeamId) {
                secondTeamSubmits.push(submit);
            }
        }
    }
    const submitsForTeams = calculateSubmitsForTeams(submits);

    const rows = [];
    const deltas = [];
    let count = 0;
    const firstTeamData = Object.hasOwn(submitsForTeams, firstTeamId) ? submitsForTeams[firstTeamId] : {};
    const secondTeamData = Object.hasOwn(submitsForTeams, secondTeamId) ? submitsForTeams[secondTeamId] : {};
    tasks.forEach((task: TaskModel, id) => {
        const firstSubmit = Object.hasOwn(firstTeamData, task.taskId) ? firstTeamData[task.taskId] : null;
        const secondSubmit = Object.hasOwn(secondTeamData, task.taskId) ? secondTeamData[task.taskId] : null;
        let delta = 0;
        if (firstSubmit && secondSubmit) {
            count++;
            delta = Math.abs(firstSubmit.timestamp - secondSubmit.timestamp);
            deltas.push(delta);
        }
        rows.push(<tr key={id}>
            <td>{task.label}</td>
            <td>{firstSubmit ? firstSubmit.modified : ''}</td>
            <td>{secondSubmit ? secondSubmit.modified : ''}</td>
            <td>{delta ? (getTimeLabel(delta, 0)) : ''}</td>
        </tr>);

    });
    const avgNStdDev = getAverageNStandardDeviation(deltas);
    return <div>
        <table className="table table-striped table-hover table-sm">
            <thead>
            <tr>
                <th>{translator.getText('Task')}</th>
                <th>{translator.getText('Time first team')}</th>
                <th>{translator.getText('Time second team')}</th>
                <th>{translator.getText('Difference')}</th>
            </tr>

            </thead>
            <tbody>{rows}</tbody>
        </table>
        <p>
            <span>{firstTeamSubmits.length} {translator.getText('first team')}</span>
            <span>{secondTeamSubmits.length} {translator.getText('second team')}</span>
            <span>{count} {translator.getText('both teams')}</span>
            <span>{getTimeLabel(avgNStdDev.average, avgNStdDev.standardDeviation)} {translator.getText('per task')}</span>
        </p>
    </div>;
}

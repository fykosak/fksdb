import { SubmitModel, Submits } from 'FKSDB/Models/ORM/Models/Fyziklani/SubmitModel';
import { TaskModel } from 'FKSDB/Models/ORM/Models/Fyziklani/TaskModel';
import { TeamModel } from 'FKSDB/Models/ORM/Models/Fyziklani/TeamModel';
import * as React from 'react';
import { connect } from 'react-redux';
import { getTimeLabel } from '../Middleware/correlation';
import { getAverageNStandardDeviation } from '../Middleware/stdDev';
import { calculateSubmitsForTeams } from '../Middleware/submitsForTeams';
import { Store } from 'FKSDB/Components/Game/ResultsAndStatistics/reducers/store';
import { TranslatorContext } from '@translator/LangContext';

interface StateProps {
    submits: Submits;
    tasks: TaskModel[];
    teams: TeamModel[];
    firstTeamId: number;
    secondTeamId: number;
}

class Table extends React.Component<StateProps> {
    static contextType = TranslatorContext;
    public render() {
        const translator = this.context;
        const {firstTeamId, secondTeamId, submits, tasks} = this.props;
        const firstTeamSubmits: SubmitModel[] = [];
        const secondTeamSubmits: SubmitModel[] = [];
        for (const id in submits) {
            if (submits.hasOwnProperty(id)) {
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
        const firstTeamData = submitsForTeams.hasOwnProperty(firstTeamId) ? submitsForTeams[firstTeamId] : {};
        const secondTeamData = submitsForTeams.hasOwnProperty(secondTeamId) ? submitsForTeams[secondTeamId] : {};
        tasks.forEach((task: TaskModel, id) => {
            const firstSubmit = firstTeamData.hasOwnProperty(task.taskId) ? firstTeamData[task.taskId] : null;
            const secondSubmit = secondTeamData.hasOwnProperty(task.taskId) ? secondTeamData[task.taskId] : null;
            let delta = 0;
            if (firstSubmit && secondSubmit) {
                count++;
                delta = Math.abs(firstSubmit.timestamp - secondSubmit.timestamp);
                deltas.push(delta);
            }
            rows.push(<tr key={id}>
                <td>{task.label}</td>
                <td>{firstSubmit ? firstSubmit.created : ''}</td>
                <td>{secondSubmit ? secondSubmit.created : ''}</td>
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
}

const mapStateToProps = (state: Store): StateProps => {
    return {
        firstTeamId: state.statistics.firstTeamId,
        secondTeamId: state.statistics.secondTeamId,
        submits: state.data.submits,
        tasks: state.data.tasks,
        teams: state.data.teams,
    };
};

export default connect(mapStateToProps, null)(Table);

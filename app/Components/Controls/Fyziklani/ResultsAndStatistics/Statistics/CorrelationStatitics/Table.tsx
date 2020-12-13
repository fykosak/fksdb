import { Submits } from '@FKSDB/Model/FrontEnd/apps/fyziklani/helpers/interfaces';
import { ModelFyziklaniSubmit } from '@FKSDB/Model/ORM/Models/Fyziklani/modelFyziklaniSubmit';
import { ModelFyziklaniTask } from '@FKSDB/Model/ORM/Models/Fyziklani/modelFyziklaniTask';
import { ModelFyziklaniTeam } from '@FKSDB/Model/ORM/Models/Fyziklani/modelFyziklaniTeam';
import { translator } from '@translator/translator';
import * as React from 'react';
import { connect } from 'react-redux';
import { getTimeLabel } from '../Middleware/correlation';
import { getAverageNStandardDeviation } from '../Middleware/stdDev';
import { calculateSubmitsForTeams } from '../Middleware/submitsForTeams';
import { Store as StatisticsStore } from '../Reducers';

interface StateProps {
    submits: Submits;
    tasks: ModelFyziklaniTask[];
    teams: ModelFyziklaniTeam[];
    firstTeamId: number;
    secondTeamId: number;
}

class Table extends React.Component<StateProps, {}> {

    public render() {

        const {firstTeamId, secondTeamId, submits, tasks} = this.props;
        const firstTeamSubmits: ModelFyziklaniSubmit[] = [];
        const secondTeamSubmits: ModelFyziklaniSubmit[] = [];
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
        tasks.forEach((task: ModelFyziklaniTask, id) => {
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
            <table className={'table table-striped table-hover table-sm'}>
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

const mapStateToProps = (state: StatisticsStore): StateProps => {
    return {
        firstTeamId: state.statistics.firstTeamId,
        secondTeamId: state.statistics.secondTeamId,
        submits: state.data.submits,
        tasks: state.data.tasks,
        teams: state.data.teams,
    };
};

export default connect(mapStateToProps, null)(Table);

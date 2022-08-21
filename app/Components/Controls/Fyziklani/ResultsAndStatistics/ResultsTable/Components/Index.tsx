import { translator } from '@translator/translator';
import { Submits } from 'FKSDB/Models/ORM/Models/Fyziklani/modelFyziklaniSubmit';
import { ModelFyziklaniTask } from 'FKSDB/Models/ORM/Models/Fyziklani/modelFyziklaniTask';
import { ModelFyziklaniTeam } from 'FKSDB/Models/ORM/Models/Fyziklani/modelFyziklaniTeam';
import * as React from 'react';
import { connect } from 'react-redux';
import { FyziklaniStatisticsTableStore } from '../../ResultsTable/reducers';
import { Filter } from '../filter';
import Row from './Row';

interface StateProps {
    filter: Filter | null;
    submits: Submits;
    teams: ModelFyziklaniTeam[];
    tasks: ModelFyziklaniTask[];
}

class Index extends React.Component<StateProps> {

    public render() {
        const {submits, teams, tasks, filter} = this.props;
        const submitsForTeams = {};
        for (const index in submits) {
            if (submits.hasOwnProperty(index)) {
                const submit = submits[index];
                const {teamId, taskId: taskId} = submit;
                submitsForTeams[teamId] = submitsForTeams[teamId] || {};
                submitsForTeams[teamId][taskId] = submit;
            }
        }
        return <div className="mb-3 fyziklani-statistics-table">
            <h1>{filter ? filter.getHeadline() : translator.getText('Results of Fyziklani')}</h1>
            <table className="table-striped table-hover table table-sm bg-white">
                <thead>
                <tr>
                    <th/>
                    <th/>
                    <th>∑</th>
                    <th>∑</th>
                    <th>x̄</th>
                    {tasks.map((task: ModelFyziklaniTask, taskIndex) =>
                        <th key={taskIndex} data-task-label={task.label}>{task.label}</th>)}
                </tr>
                </thead>
                <tbody>
                {teams.map((team: ModelFyziklaniTeam, teamIndex) =>
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
}

const mapStateToProps = (state: FyziklaniStatisticsTableStore): StateProps => {
    return {
        filter: state.tableFilter.filter,
        submits: state.data.submits,
        tasks: state.data.tasks,
        teams: state.data.teams,
    };
};

export default connect(mapStateToProps, null)(Index);

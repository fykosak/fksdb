import { Submits } from '@apps/fyziklani/helpers/interfaces';
import * as React from 'react';
import { connect } from 'react-redux';
import { Filter } from '../middleware/filters/filter';
import { FyziklaniResultsTableStore } from '../reducers';
import Row from './row';
import { ModelFyziklaniTeam } from '@FKSDB/Model/ORM/Models/Fyziklani/ModelFyziklaniTeam';
import { ModelFyziklaniTask } from '@FKSDB/Model/ORM/Models/Fyziklani/ModelFyziklaniTask';
import { translator } from '@translator/Translator';

interface StateProps {
    filter: Filter;
    submits: Submits;
    teams: ModelFyziklaniTeam[];
    tasks: ModelFyziklaniTask[];
}

class App extends React.Component<StateProps, {}> {

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

        const headCools = tasks.map((task: ModelFyziklaniTask, taskIndex) => {
            return (<th key={taskIndex} data-task_label={task.label}>{task.label}</th>);
        });

        return (
            <div className="mb-3">
                <h1>{filter ? filter.getHeadline() : translator.getText('Results of Fyziklani')}</h1>
                <table className="table-striped table-hover table table-sm bg-white">
                    <thead>
                    <tr>
                        <th/>
                        <th/>
                        <th>∑</th>
                        <th>∑</th>
                        <th>x̄</th>
                        {headCools}
                    </tr>
                    </thead>
                    <tbody>
                    {teams.map((team: ModelFyziklaniTeam, teamIndex) => {
                        return (
                            <Row
                                tasks={tasks}
                                submits={submitsForTeams[team.teamId] || {}}
                                team={team}
                                key={teamIndex}
                                visible={(filter ? filter.match(team) : true)}
                            />
                        );
                    })}
                    </tbody>
                </table>
            </div>
        );
    }
}

const mapStateToProps = (state: FyziklaniResultsTableStore): StateProps => {
    const {index, filters} = state.tableFilter;
    return {
        filter: (filters.hasOwnProperty(index)) ? filters[index] : null,
        submits: state.data.submits,
        tasks: state.data.tasks,
        teams: state.data.teams,
    };
};

export default connect(mapStateToProps, null)(App);

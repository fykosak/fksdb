import { Submits } from 'FKSDB/Models/ORM/Models/Fyziklani/SubmitModel';
import { TaskModel } from 'FKSDB/Models/ORM/Models/Fyziklani/TaskModel';
import { TeamModel } from 'FKSDB/Models/ORM/Models/Fyziklani/TeamModel';
import * as React from 'react';
import { connect } from 'react-redux';
import { Filter } from '../filter';
import Row from './Row';
import { Store } from 'FKSDB/Components/Game/ResultsAndStatistics/reducers/store';
import { TranslatorContext } from '@translator/LangContext';

interface StateProps {
    filter: Filter | null;
    submits: Submits;
    teams: TeamModel[];
    tasks: TaskModel[];
}

class Index extends React.Component<StateProps> {
    static contextType = TranslatorContext;
    public render() {
        const translator = this.context;
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
}

const mapStateToProps = (state: Store): StateProps => {
    return {
        filter: state.tableFilter.filter,
        submits: state.data.submits,
        tasks: state.data.tasks,
        teams: state.data.teams,
    };
};

export default connect(mapStateToProps, null)(Index);

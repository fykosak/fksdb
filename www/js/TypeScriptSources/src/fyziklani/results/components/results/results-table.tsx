import * as React from 'react';
import { findDOMNode } from 'react-dom';
import { connect } from 'react-redux';
import {
    ISubmits,
    ITask,
    ITeam,
} from '../../../helpers/interfaces';
import { IFyziklaniResultsStore } from '../../reducers';
import { Filter } from './filter/filter';
import TeamRow from './team-row';
import { lang } from '../../../../i18n/i18n';

interface IState {
    filter?: Filter;
    submits?: ISubmits;
    teams?: ITeam[];
    tasks?: ITask[];
}

class ResultsTable extends React.Component<IState, {}> {
    private table;

    public constructor(props) {
        super(props);
        this.table = null;
    }

    public componentDidUpdate() {
        const table = $(findDOMNode(this.table));
        try {
            table.trigger('update');
            table.trigger('sorton', [[[2, 1], [4, 1]]]);
        } catch (error) {
            console.error(error);
        }
    }

    public componentDidMount() {
        const table: any = $(findDOMNode(this.table));
        table.tablesorter();
    }

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

        const headCools = tasks.map((task: ITask, taskIndex) => {
            return (<th key={taskIndex} data-task_label={task.label}>{task.label}</th>);
        });

        return (
            <div className="mb-3">
                <h1>{filter ? filter.getHeadline() : lang.getText('Results of Fyziklani')}</h1>
                <table ref={(table) => {
                    this.table = table;
                }} className="tablesorter table-striped table-hover">
                    <thead>
                    <tr>
                        <th/>
                        <th/>
                        <th>∑</th>
                        <th>N</th>
                        <th>x̄</th>
                        {headCools}
                    </tr>
                    </thead>
                    <tbody>
                    {teams.map((team: ITeam, teamIndex) => {
                        return (
                            <TeamRow
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

const mapStateToProps = (state: IFyziklaniResultsStore): IState => {
    const {index, filters} = state.tableFilter;
    return {
        filter: (filters.hasOwnProperty(index)) ? filters[index] : null,
        submits: state.data.submits,
        tasks: state.data.tasks,
        teams: state.data.teams,
    };
};

export default connect(mapStateToProps, null)(ResultsTable);

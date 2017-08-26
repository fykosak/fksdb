import * as React from 'react';
import { findDOMNode } from 'react-dom';
import { connect } from 'react-redux';

import { Filter } from '../../../helpers/filters/filters';
import { createFilter } from '../../../helpers/filters/table-filter';
import {
    ISubmits,
    ITask,
    ITeam,
} from '../../../helpers/interfaces';
import { IStore } from '../../../reducers/index';
import FilterButtons from './filter-buttons';
import TeamRow from './team-row';

interface IState {
    filter?: Filter;
    submits?: ISubmits;
    teams?: ITeam[];
    tasks?: ITask[];
}

class ResultsTable extends React.Component<IState, {}> {
    private table;

    public constructor() {
        super();
        this.table = null;
    }

    public componentDidUpdate() {
        const $table = $(findDOMNode(this.table));
        try {
            $table.trigger('update');
            $table.trigger('sorton', [[[1, 1], [3, 1]]]);
        } catch (error) {
            console.error(error);
        }
    }

    public componentDidMount() {
        const $table: any = $(findDOMNode(this.table));
        $table.tablesorter();
    }

    public render() {
        const { submits, teams, tasks, filter } = this.props;
        const submitsForTeams = {};
        for (const index in submits) {
            if (submits.hasOwnProperty(index)) {
                const submit = submits[index];
                const { team_id: teamId, task_id: taskId } = submit;
                submitsForTeams[teamId] = submitsForTeams[teamId] || {};
                submitsForTeams[teamId][taskId] = submit;
            }
        }

        const headCools = tasks.map((task: ITask, taskIndex) => {
            return (<th key={taskIndex} data-task_label={task.label}>{task.label}</th>);
        });

        return (
            <div className="mb-3">
                <FilterButtons/>
                <h1>{filter.getHeadline()}</h1>
                <table ref={(table) => {
                    this.table = table;
                }} className="tablesorter table-striped table-hover">
                    <thead>
                    <tr>
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
                                submits={submitsForTeams[team.team_id] || {}}
                                team={team}
                                key={teamIndex}
                                visible={(filter && filter.match(team))}
                            />
                        );
                    })}
                    </tbody>
                </table>
            </div>
        );
    }
}

const mapStateToProps = (state: IStore): IState => {
    const { filterID, room, category, userFilter, autoSwitch } = state.tableFilter;
    return {
        filter: createFilter(filterID, autoSwitch, { room, category }, userFilter),
        submits: state.results.submits,
        tasks: state.results.tasks,
        teams: state.results.teams,
    };
};

export default connect(mapStateToProps, null)(ResultsTable);

import * as React from 'react';
import * as ReactDOM from 'react-dom';

import {connect} from 'react-redux';

import TeamRow from './team-row';
import {
    ITeam,
    ITask,
} from '../../helpers/interfaces';
import {createFilter} from '../../helpers/table-filter';
import {IFilter} from '../../helpers/filters';

interface IResultsTable {
    filter?: IFilter;
    submits?: any;
    teams?: Array<ITeam>;
    tasks?: Array<ITask>;
    displayCategory: string;
    displayRoom: string;
}

class ResultsTable extends React.Component<IResultsTable, void> {
    private table;

    public constructor() {
        super();
        this.table = null;
    }

    public componentDidUpdate() {
        let $table = $(ReactDOM.findDOMNode(this.table));
        try {
            $table.trigger("update");
            $table.trigger("sorton", [[[1, 1], [3, 1]]]);
        } catch (error) {
            console.error(error);
        }
    }

    public componentDidMount() {
        let $table = $(ReactDOM.findDOMNode(this.table));
        //   $table.tablesorter()
    }

    public render() {

        const {submits, teams, tasks, displayCategory, displayRoom} = this.props;
        const submitsForTeams = {};
        console.log(submits);
        for (let index in submits) {
            if (submits.hasOwnProperty(index)) {
                const submit = submits[index];
                const {team_id, task_id} = submit;
                submitsForTeams[team_id] = submitsForTeams[team_id] || {};
                submitsForTeams[team_id][task_id] = submit;
            }
        }

        const rows = teams.map((team: ITeam, teamIndex) => {
            return (
                <TeamRow
                    tasks={tasks}
                    submits={submitsForTeams[team.team_id] || {}}
                    team={team}
                    key={teamIndex}
                />
            );
        });

        const headCools = tasks.map((task: ITask, taskIndex) => {
            return (<th key={taskIndex} data-task_label={task.label}>{task.label}</th>);
        });

        return (
            <div>

                <table ref={(table)=>{this.table = table}} className="tablesorter">
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
                    {rows}
                    </tbody>
                </table>
            </div>
        );
    }
}

const mapStateToProps = (state, ownProps) => {
    debugger;
    const {filterID, room, category, userFilter, autoSwitch} = state.tableFilter;
    return {
        ...ownProps,
        teams: state.results.teams,
        tasks: state.results.tasks,
        submits: state.results.submits,
        filter: createFilter(filterID, autoSwitch, {room, category}, userFilter),
    };
};

export default connect(mapStateToProps, null)(ResultsTable);

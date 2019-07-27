import * as React from 'react';
import { findDOMNode } from 'react-dom';
import { connect } from 'react-redux';
import { lang } from '../../../../../i18n/i18n';
import {
    Submits,
    Task,
    Team,
} from '../../../../helpers/interfaces';
import { Filter } from '../../../middleware/results/filters/filter';
import { FyziklaniResultsStore } from '../../../reducers';
import Row from './Row';

interface State {
    filter?: Filter;
    submits?: Submits;
    teams?: Team[];
    tasks?: Task[];
}

class Index extends React.Component<State, {}> {
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

        const headCools = tasks.map((task: Task, taskIndex) => {
            return (<th key={taskIndex} data-task_label={task.label}>{task.label}</th>);
        });

        return (
            <div className="mb-3">
                <h1>{filter ? filter.getHeadline() : lang.getText('Results of Fyziklani')}</h1>
                <table ref={(table) => {
                    this.table = table;
                }} className="tablesorter table-striped table-hover table table-sm bg-white">
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
                    {teams.map((team: Team, teamIndex) => {
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

const mapStateToProps = (state: FyziklaniResultsStore): State => {
    const {index, filters} = state.tableFilter;
    return {
        filter: (filters.hasOwnProperty(index)) ? filters[index] : null,
        submits: state.data.submits,
        tasks: state.data.tasks,
        teams: state.data.teams,
    };
};

export default connect(mapStateToProps, null)(Index);

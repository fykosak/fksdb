import * as React from 'react';
import { connect } from 'react-redux';
import { lang } from '../../../../i18n/i18n';
import {
    ISubmit,
    ISubmits,
    ITask,
    ITeam,
} from '../../../helpers/interfaces';
import { IFyziklaniResultsStore } from '../../reducers';
import { Filter } from './filter/filter';
import TeamPresentationRow from './TeamPresentationRow';

interface IState {
    filter?: Filter;
    submits?: ISubmits;
    teams?: ITeam[];
    tasks?: ITask[];
    cols?: number;
    rows?: number;
    position?: number;
}

interface IItem {
    teamId: number;
    submits: {
        [taskId: number]: ISubmit;
    };
    points: number;
    groups: {
        1: number;
        2: number;
        3: number;
        5: number;
    };
    count: number;
}

class ResultsPresentation extends React.Component<IState, {}> {

    public render() {
        const {submits, teams, filter, rows, cols} = this.props;
        let {position} = this.props;
        const submitsForTeams: {
            [teamId: number]: IItem;
        } = {};
        for (const index in submits) {
            if (submits.hasOwnProperty(index)) {
                const submit = submits[index];
                if (!submit.points) {
                    continue;
                }

                const {teamId, taskId: taskId} = submit;
                submitsForTeams[teamId] = submitsForTeams[teamId] || {
                    count: 0,
                    groups: {1: 0, 2: 0, 3: 0, 5: 0},
                    points: 0,
                    submits: {},
                    teamId,
                };
                submitsForTeams[teamId].submits[taskId] = submit;
                submitsForTeams[teamId].points += +submit.points;
                submitsForTeams[teamId].count++;
                submitsForTeams[teamId].groups[submit.points]++;

            }
        }

        const submitsForTeamsArray: IItem[] = [];
        for (const teamId in submitsForTeams) {
            if (submitsForTeams.hasOwnProperty(teamId)) {
                submitsForTeamsArray.push(submitsForTeams[teamId]);
            }
        }
        submitsForTeamsArray.sort((a, b) => {
            return b.points - a.points;
        });
        const availablePoints = [5, 3, 2, 1];

        const resultsItems = [];
        for (let col = 0; col < cols; col++) {

            const colItems = [];
            for (let row = 0; row < rows; row++) {
                if (submitsForTeamsArray.hasOwnProperty(position)) {
                    const item = submitsForTeamsArray[position];
                    position++;

                    const [selectedTeam] = teams.filter((team: ITeam) => {
                        return team.teamId === item.teamId;
                    });
                    if (!selectedTeam) {
                        console.log('team ' + item.teamId + ' nexistuje');
                        continue;
                    }
                    colItems.push(<TeamPresentationRow key={item.teamId} item={item} position={position} team={selectedTeam}
                                                       availablePoints={availablePoints}/>);
                }

            }
            switch (cols) {
                case 2:
                    resultsItems.push(<div className={'col-5'} key={col}>{colItems}</div>);
                    break;
                case 3:
                    resultsItems.push(<div className={'col-3'} key={col}>{colItems}</div>);
                    break;
                default:
                case 1:
                    resultsItems.push(<div className={'col-10'} key={col}>{colItems}</div>);
            }

        }
        return (
            <div className="mb-3">
                <h1>{filter ? filter.getHeadline() : lang.getText('Results of Fyziklani')}</h1>
                <div className={'row justify-content-around'}>{resultsItems}</div>
            </div>
        );
    }
}

const mapStateToProps = (state: IFyziklaniResultsStore): IState => {
    const {index, filters} = state.tableFilter;
    return {
        cols: state.presentation.cols,
        filter: (filters.hasOwnProperty(index)) ? filters[index] : null,
        position: state.presentation.position,
        rows: state.presentation.rows,
        submits: state.data.submits,
        tasks: state.data.tasks,
        teams: state.data.teams,
    };
};

export default connect(mapStateToProps, null)(ResultsPresentation);

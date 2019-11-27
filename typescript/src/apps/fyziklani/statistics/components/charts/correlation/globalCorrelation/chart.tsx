import { lang } from '@i18n/i18n';
import { scaleLinear } from 'd3-scale';
import * as React from 'react';
import { findDOMNode } from 'react-dom';
import { connect } from 'react-redux';
import {
    Action,
    Dispatch,
} from 'redux';
import {
    Submits,
    Task,
    Team,
} from '../../../../../helpers/interfaces';
import {
    setFirstTeamId,
    setSecondTeamId,
} from '../../../../actions';
import {
    calculateCorrelation,
    getTimeLabel,
} from '../../../../middleware/charts/correlation';
import { calculateSubmitsForTeams } from '../../../../middleware/charts/submitsForTeams';
import { Store as StatisticsStore } from '../../../../reducers';

interface StateProps {
    submits: Submits;
    tasks: Task[];
    teams: Team[];
    firstTeamId: number;
    secondTeamId: number;
}

interface DispatchProps {
    onChangeFirstTeam(id: number): void;

    onChangeSecondTeam(id: number): void;
}

class GlobalCorrelation extends React.Component<StateProps & DispatchProps, {}> {
    private table;

    public componentDidMount() {
        const table: any = $(findDOMNode(this.table));
      //  table.tablesorter();
    }

    public render() {

        const color = scaleLinear<string, string>().domain([0, 1000 * 1000]).range(['#ff0000', '#ffffff']);
        const {submits, teams} = this.props;
        const submitsForTeams = calculateSubmitsForTeams(submits);
        const rows = [];
        teams.forEach((firstTeam) => {
            teams.forEach((secondTeam) => {
                if (secondTeam.teamId <= firstTeam.teamId) {
                    return;
                }
                const {avgNStdDev, countFiltered, countTotal} = calculateCorrelation(
                    submitsForTeams.hasOwnProperty(firstTeam.teamId) ? submitsForTeams[firstTeam.teamId] : {},
                    submitsForTeams.hasOwnProperty(secondTeam.teamId) ? submitsForTeams[secondTeam.teamId] : {},
                );
                rows.push(<tr key={secondTeam.teamId + '__' + firstTeam.teamId}>
                    <td>{firstTeam.name}</td>
                    <td>{secondTeam.name}</td>
                    <td style={{backgroundColor: color(avgNStdDev.average)}}>
                        {getTimeLabel(avgNStdDev.average, avgNStdDev.standardDeviation)}
                    </td>
                    <td>{countFiltered}</td>
                    <td>{countTotal}</td>
                    <td>
                        <span className={'btn btn-primary btn-sm'} onClick={() => {
                            this.props.onChangeFirstTeam(firstTeam.teamId);
                            this.props.onChangeSecondTeam(secondTeam.teamId);
                        }}>Detail</span>
                    </td>
                </tr>);

            });
        });
        return <table className={'table table-striped tablesorter table-sm'}
                      ref={(table) => {
                          this.table = table;
                      }}
        >
            <thead>
            <tr>
                <th>{lang.getText('First team')}</th>
                <th>{lang.getText('Second team')}</th>
                <th>{lang.getText('Average')}</th>
                <th>{lang.getText('Under 2 minutes')}</th>
                <th>{lang.getText('Both teams')}</th>
            </tr>
            </thead>
            <tbody>{rows}</tbody>
        </table>;
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

const mapDispatchToProps = (dispatch: Dispatch<Action<string>>): DispatchProps => {
    return {
        onChangeFirstTeam: (teamId) => dispatch(setFirstTeamId(+teamId)),
        onChangeSecondTeam: (teamId) => dispatch(setSecondTeamId(+teamId)),
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(GlobalCorrelation);

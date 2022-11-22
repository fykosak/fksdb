import { translator } from '@translator/translator';
import ChartContainer from 'FKSDB/Components/Charts/Core/ChartContainer';
import { TeamModel } from 'FKSDB/Models/ORM/Models/Fyziklani/TeamModel';
import * as React from 'react';
import { connect } from 'react-redux';
import {
    Action,
    Dispatch,
} from 'redux';
import { setNewState } from '../actions';
import { StatisticStore } from '../Reducers';
import Legend from './Legend';
import PointsInTime from './LineChart';
import PointsPie from './PieChart';
import TimeLine from './Timeline';

interface StateProps {
    teams: TeamModel[];
    teamId: number;
}

interface DispatchProps {
    onChangeFirstTeam(id: number): void;
}

class TeamStats extends React.Component<StateProps & DispatchProps> {

    public render() {
        const {teams, onChangeFirstTeam, teamId} = this.props;

        const teamSelect = (
            <p>
                <select className="form-control" onChange={(event) => {
                    onChangeFirstTeam(+event.target.value);
                }}>
                    <option value={null}>--{translator.getText('select team')}--</option>
                    {teams.map((team) => {
                        return (<option key={team.teamId} value={team.teamId}>{team.name}</option>);
                    })}
                </select>
            </p>
        );
        const selectedTeam = teams.filter((team) => {
            return team.teamId === teamId;
        })[0];
        return (<>
            {teamSelect}
            {teamId && (<>
                <h2>
                    {translator.getText('Statistic for team ') + (selectedTeam ? selectedTeam.name : '')}
                </h2>
                <ChartContainer
                    chart={PointsPie}
                    chartProps={{teamId}}
                    legendComponent={Legend}
                    headline={translator.getText('Success of submitting')}
                />
                <hr/>
                <ChartContainer
                    chart={PointsInTime}
                    chartProps={{teamId}}
                    legendComponent={Legend}
                    headline={translator.getText('Time progress')}
                />
                <hr/>
                <ChartContainer
                    chart={TimeLine}
                    chartProps={{teamId}}
                    legendComponent={Legend}
                    headline={translator.getText('Timeline')}
                />
            </>)}
        </>);
    }
}

const mapStateToProps = (state: StatisticStore): StateProps => {
    return {
        teamId: state.statistics.firstTeamId,
        teams: state.data.teams,
    };
};

const mapDispatchToProps = (dispatch: Dispatch<Action<string>>): DispatchProps => {
    return {
        onChangeFirstTeam: (teamId) => dispatch(setNewState({firstTeamId: +teamId})),
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(TeamStats);

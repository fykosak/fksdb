import { Team } from '@apps/fyziklani/helpers/interfaces';
import { lang } from '@i18n/i18n';
import ChartContainer from '@shared/components/chartContainer';
import * as React from 'react';
import { connect } from 'react-redux';
import {
    Action,
    Dispatch,
} from 'redux';
import { setFirstTeamId } from '../../actions';
import { Store as StatisticsStore } from '../../reducers';
import Legend from './legend';
import PointsInTime from './lineChart';
import PointsPie from './pieChart';
import TimeLine from './timeline';

interface StateProps {
    teams: Team[];
    teamId: number;
}

interface DispatchProps {
    onChangeFirstTeam(id: number): void;
}

class TeamStats extends React.Component<StateProps & DispatchProps, {}> {

    public render() {
        const {teams, onChangeFirstTeam, teamId} = this.props;

        const teamSelect = (
            <p>
                <select className="form-control" onChange={(event) => {
                    onChangeFirstTeam(+event.target.value);
                }}>
                    <option value={null}>--{lang.getText('select team')}--</option>
                    {teams.map((team) => {
                        return (<option key={team.teamId} value={team.teamId}>{team.name}</option>);
                    })}
                </select>
            </p>
        );
        const selectedTeam = teams.filter((team) => {
            return team.teamId === teamId;
        })[0];
//
        const headline = (
            <h2 className={'fyziklani-headline'}>
                {lang.getText('Statistic for team ') + (selectedTeam ? selectedTeam.name : '')}
            </h2>);
        return (<div>

            {teamSelect}
            {teamId && (<>
                {headline}
                <ChartContainer
                    chart={PointsPie}
                    chartProps={{teamId}}
                    legendComponent={Legend}
                    headline={lang.getText('Success of submitting')}
                />
                <hr/>
                <ChartContainer
                    chart={PointsInTime}
                    chartProps={{teamId}}
                    legendComponent={Legend}
                    headline={lang.getText('Time progress')}
                />
                <hr/>
                <ChartContainer
                    chart={TimeLine}
                    chartProps={{teamId}}
                    legendComponent={Legend}
                    headline={lang.getText('Timeline')}
                />
            </>)}
        </div>);
    }
}

const mapStateToProps = (state: StatisticsStore): StateProps => {
    return {
        teamId: state.statistics.firstTeamId,
        teams: state.data.teams,
    };
};

const mapDispatchToProps = (dispatch: Dispatch<Action<string>>): DispatchProps => {
    return {
        onChangeFirstTeam: (teamId) => dispatch(setFirstTeamId(+teamId)),
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(TeamStats);

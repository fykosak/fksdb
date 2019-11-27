import { lang } from '@i18n/i18n';
import * as React from 'react';
import { connect } from 'react-redux';
import {
    Action,
    Dispatch,
} from 'redux';
import { Team } from '../../../../helpers/interfaces';
import { setFirstTeamId } from '../../../actions';
import { Store as StatisticsStore } from '../../../reducers';
import PointsInTime from './lineChart/';
import PointsPie from './pie/';
import TimeLine from './timeline/';

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
                <PointsPie teamId={teamId}/>
                <hr/>
                <PointsInTime teamId={teamId}/>
                <hr/>
                <TimeLine teamId={teamId}/>
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

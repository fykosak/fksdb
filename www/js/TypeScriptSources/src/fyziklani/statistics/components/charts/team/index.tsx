import * as React from 'react';
import { connect } from 'react-redux';
import {
    Action,
    Dispatch,
} from 'redux';
import { lang } from '../../../../../i18n/i18n';
import { ITeam } from '../../../../helpers/interfaces';
import { setFirstTeamId } from '../../../actions';
import { IFyziklaniStatisticsStore } from '../../../reducers';
import PointsInTime from './line-chart/index';
import PointsPie from './pie/index';
import TimeLine from './timeline/index';

interface IState {
    teams?: ITeam[];
    teamId?: number;

    onChangeFirstTeam?(id: number): void;
}

class TeamStats extends React.Component<IState, {}> {

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

const mapStateToProps = (state: IFyziklaniStatisticsStore): IState => {
    return {
        teamId: state.statistics.firstTeamId,
        teams: state.data.teams,
    };
};

const mapDispatchToProps = (dispatch: Dispatch<Action<string>>): IState => {
    return {
        onChangeFirstTeam: (teamId) => dispatch(setFirstTeamId(+teamId)),
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(TeamStats);

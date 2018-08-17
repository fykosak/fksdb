import * as React from 'react';
import {
    connect,
    Dispatch,
} from 'react-redux';
import { lang } from '../../../../../i18n/i18n';
import { ITeam } from '../../../../../shared/interfaces';
import { setTeamId } from '../../../actions';
import { IFyziklaniStatisticsStore } from '../../../reducers';
import PointsInTime from './line-chart/index';
import PointsPie from './pie/index';
import TimeLine from './timeline/index';

interface IState {
    teams?: ITeam[];
    onchangeTeam?: (id: number) => void;
    teamId?: number;
}

class TeamStats extends React.Component<IState, {}> {

    public render() {
        const {teams, onchangeTeam, teamId} = this.props;

        const teamSelect = (
            <p>
                <select className="form-control" onChange={(event) => {
                    onchangeTeam(+event.target.value);
                }}>
                    <option value={null}>--select team--</option>
                    {teams.map((team) => {
                        return (<option key={team.teamId} value={team.teamId}>{team.name}</option>);
                    })}
                </select>
            </p>
        );

        return (<div>
            <h2>{lang.getText('teamStatistics')}</h2>
            {teamSelect}
            {teamId && (<PointsPie/>)}
            {teamId && (<PointsInTime/>)}
            {teamId && (<TimeLine/>)}
        </div>);
    }
}

const mapStateToProps = (state: IFyziklaniStatisticsStore): IState => {
    return {
        teamId: state.statistics.teamId,
        teams: state.data.teams,
    };
};

const mapDispatchToProps = (dispatch: Dispatch<IFyziklaniStatisticsStore>): IState => {
    return {
        onchangeTeam: (teamId) => dispatch(setTeamId(+teamId)),
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(TeamStats);

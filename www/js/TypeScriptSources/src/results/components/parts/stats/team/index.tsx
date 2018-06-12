import * as React from 'react';
import {
    connect,
    Dispatch,
} from 'react-redux';
import Lang from '../../../../../lang/components/lang';
import { ITeam } from '../../../../../shared/interfaces';
import { setTeamId } from '../../../../actions/stats';
import { IStore } from '../../../../reducers/';
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
            <h2><Lang text={'teamStatistics'}/></h2>
            {teamSelect}
            {teamId && (<PointsPie/>)}
            {teamId && (<PointsInTime/>)}
            {teamId && (<TimeLine/>)}
        </div>);
    }
}

const mapStateToProps = (state: IStore): IState => {
    return {
        teamId: state.stats.teamId,
        teams: state.results.teams,
    };
};

const mapDispatchToProps = (dispatch: Dispatch<IStore>): IState => {
    return {
        onchangeTeam: (teamId) => dispatch(setTeamId(+teamId)),
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(TeamStats);

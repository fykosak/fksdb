import * as React from 'react';
import {
    connect,
    Dispatch,
} from 'react-redux';
import { setTeamID } from '../../../../actions/stats';
import { ITeam } from '../../../../helpers/interfaces';
import { IStore } from '../../../../reducers/index';

import PointsInTime from './line-chart/index';
import PointsPie from './pie/index';
import TimeLine from './timeline/index';

interface IState {
    teams?: ITeam[];
    onchangeTeam?: (id: number) => void;
    teamID?: number;
}

class TeamStats extends React.Component<IState, {}> {

    public render() {
        const { teams, onchangeTeam, teamID } = this.props;

        const teamSelect = (
            <p>
                <select className="form-control" onChange={(event) => {
                    onchangeTeam(+event.target.value);
                }}>
                    <option value={null}>--select team--</option>
                    {teams.map((team) => {
                        return (<option value={team.team_id}>{team.name}</option>);
                    })}
                </select>
            </p>
        );

        return (<div>
            <h2>Team statistics</h2>
            {teamSelect}
            {teamID && (<PointsPie/>)}
            {teamID && (<PointsInTime/>)}
            {teamID && (<TimeLine/>)}
        </div>);
    }
}

const mapStateToProps = (state: IStore): IState => {
    return {
        teamID: state.stats.teamID,
        teams: state.results.teams,
    };
};

const mapDispatchToProps = (dispatch: Dispatch<IStore>): IState => {
    return {
        onchangeTeam: (teamID) => dispatch(setTeamID(+teamID)),
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(TeamStats);

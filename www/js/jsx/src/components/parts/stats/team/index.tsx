import * as React from 'react';
import {connect} from 'react-redux';
import PointsPie from './pie/index';
import PointsInTime from './line-chart/index';
import TimeLine from './timeline/index';
import {ITeam} from '../../../../helpers/interfaces';
import {setTeamID} from '../../../../actions/stats';

interface IProps {
    teams: Array<ITeam>;
    onchangeTeam: Function;
    teamID: number;
}

class TeamStats extends React.Component<IProps, void> {

    render() {
        const {teams, onchangeTeam, teamID} = this.props;

        const teamSelect = (
            <p>
                <select className="form-control" onChange={(event) => {
                    onchangeTeam(+event.target.value)
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

const mapStateToProps = (state, ownProps) => {
    return {
        ...ownProps,
        teams: state.results.teams,
        teamID: state.stats.teamID,
    };
};

const mapDispatchToProps = (dispatch, ownProps) => {
    return {
        ...ownProps,
        onchangeTeam: (teamID) => dispatch(setTeamID(+teamID)),
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(TeamStats);

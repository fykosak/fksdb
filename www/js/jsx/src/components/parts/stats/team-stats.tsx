import * as React from 'react';
import {connect} from 'react-redux';
import PointsPie from './points-pie';
import PointsInTime from './total-points-in-time';
import {ITeam} from '../../../helpers/interfaces';

interface IProps {
    teams: Array<ITeam>;
}
interface IState {
    teamID: number;
}

class TeamStats extends React.Component<IProps, IState> {

    public constructor() {
        super();
        this.state = {teamID: null};
    }

    render() {
        const {teams} = this.props;
        const {teamID} = this.state;

        const teamSelect = (
            <div>
                <select className="form-control" onChange={(event) => {
                    this.setState({teamID: +event.target.value})
                }}>
                    <option>--select team--</option>
                    {teams.map((team) => {
                        return (<option value={team.team_id}>{team.name}</option>);
                    })}
                </select>
            </div>
        );
        return (<div>
            <h2>Team statistics</h2>
            {teamSelect}
            {teamID && (<PointsPie teamID={teamID}/>)}
            {teamID && (<PointsInTime teamID={teamID}/>)}
        </div>);
    }
}

const mapStateToProps = (state, ownProps) => {
    return {
        ...ownProps,
        teams: state.results.teams,
    };
};

export default connect(mapStateToProps, null)(TeamStats);

import * as React from 'react';
import { connect } from 'react-redux';
import { Team } from '../../../helpers/interfaces/';
import { Store as RoutingStore } from '../../reducers/';
import TeamComponent from '../team/';

interface StateProps {
    stateTeams: Team[];
}

class UnRoutedTeams extends React.Component<StateProps, {}> {

    public render() {
        const {stateTeams} = this.props;

        return (
            <div className="row">
                {stateTeams && stateTeams.filter((team) => {
                    return team.x === null && team.y === null;
                }).map((team, index) => {
                    return <TeamComponent
                        team={team}
                        key={index}
                    />;
                })}
            </div>
        );
    }
}

const mapStateToProps = (state: RoutingStore): StateProps => {
    return {
        stateTeams: state.teams.availableTeams,
    };
};

export default connect(mapStateToProps, null)(UnRoutedTeams);

import * as React from 'react';
import Team from './team';

import { connect } from 'react-redux';
import { ITeam } from '../reducers/teams';

interface IState {
    stateTeams?: ITeam[];
}
class UnRoutedTeams extends React.Component<IState, {}> {

    public render() {
        const { stateTeams } = this.props;

        return (
            <div>
                {stateTeams && stateTeams.filter((team) => {
                    return team.x === undefined && team.y === undefined;
                }).map((team, index) => {
                    return <Team
                        team={team}
                        key={index}
                    />;
                })}
            </div>
        );
    }
}

const mapStateToProps = (state): IState => {
    return {
        stateTeams: state.teams,
    };
};

const maxDispatchToProps = () => {
    return {};
};

export default connect(mapStateToProps, maxDispatchToProps)(UnRoutedTeams);

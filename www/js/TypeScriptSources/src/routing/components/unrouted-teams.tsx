import * as React from 'react';
import Team from './team';

import { connect } from 'react-redux';
import { ITeam } from '../../shared/interfaces';
import { IStore } from '../reducers/';

interface IState {
    stateTeams?: ITeam[];
}

class UnRoutedTeams extends React.Component<IState, {}> {

    public render() {
        const { stateTeams } = this.props;

        return (
            <div className="row">
                {stateTeams && stateTeams.filter((team) => {
                    return team.x === null && team.y === null;
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

const mapStateToProps = (state: IStore): IState => {
    return {
        stateTeams: state.teams,
    };
};

const maxDispatchToProps = (): IState => {
    return {};
};

export default connect(mapStateToProps, maxDispatchToProps)(UnRoutedTeams);

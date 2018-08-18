import * as React from 'react';
import { connect } from 'react-redux';
import { ITeam } from '../../helpers/interfaces';
import { IFyziklaniRoutingStore } from '../reducers/';
import Team from './team';

interface IState {
    stateTeams?: ITeam[];
}

class UnRoutedTeams extends React.Component<IState, {}> {

    public render() {
        const {stateTeams} = this.props;

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

const mapStateToProps = (state: IFyziklaniRoutingStore): IState => {
    return {
        stateTeams: state.teams.availableTeams,
    };
};

const maxDispatchToProps = (): IState => {
    return {};
};

export default connect(mapStateToProps, maxDispatchToProps)(UnRoutedTeams);

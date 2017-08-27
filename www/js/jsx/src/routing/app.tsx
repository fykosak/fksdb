import * as React from 'react';

import Rooms from './components/rooms';
import UnRoutedTeams from './components/unrouted-teams';

import { connect } from 'react-redux';
import { addTeams } from './actions/teams';
import { ITeam } from './reducers/teams';

interface IState {
    onAddTeams?: (teams: any[]) => void;
}
interface IProps {
    teams: ITeam[];
    rooms: any[];
}

class RoutingApp extends React.Component<IState & IProps, {}> {

    public componentDidMount() {
        const { onAddTeams, teams } = this.props;
        onAddTeams(teams);
    }

    public render() {
        const { rooms } = this.props;

        return (
            <div className="row">
                <div className="col-lg-8" style={{ overflowY: 'scroll', maxHeight: '700px' }}>
                    <Rooms rooms={rooms}/>
                </div>
                <div className="col-lg-4" style={{ overflowY: 'scroll', maxHeight: '700px' }}>
                    <UnRoutedTeams/>
                </div>
            </div>
        );
    }
}

const mapStateToProps = (): IState => {
    return {};
};

const mapDispatchToProps = (dispatch): IState => {
    return {
        onAddTeams: (teams) => dispatch(addTeams(teams)),
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(RoutingApp);

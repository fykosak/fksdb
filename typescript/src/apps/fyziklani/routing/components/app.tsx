import { Powered } from '@shared/powered';
import * as React from 'react';
import { connect } from 'react-redux';
import {
    Action,
    Dispatch,
} from 'redux';
import {
    Room,
    Team,
} from '../../helpers/interfaces';
import { addTeams } from '../actions/teams';
import Form from './form/';
import Rooms from './rooms/';
import UnRoutedTeams from './unroutedTeams/';

interface DispatchProps {
    onAddTeams(teams: Team[]): void;
}

interface OwnProps {
    teams: Team[];
    rooms: Room[];
}

class RoutingApp extends React.Component<DispatchProps & OwnProps, {}> {

    public componentDidMount() {
        const {onAddTeams, teams} = this.props;
        onAddTeams(teams);
    }

    public render() {
        const {rooms} = this.props;

        return (
            <div>
                <div className="row">
                    <div className="col-lg-8" style={{overflowY: 'scroll', maxHeight: '700px'}}>
                        <Rooms rooms={rooms}/>
                    </div>
                    <div className="col-lg-4" style={{overflowY: 'scroll', maxHeight: '700px'}}>
                        <UnRoutedTeams/>
                    </div>
                </div>
                <div>
                    <Form accessKey={'@@fyziklani/routing'}/>
                </div>
                <Powered/>
            </div>
        );
    }
}

const mapStateToProps = (): {} => {
    return {};
};

const mapDispatchToProps = (dispatch: Dispatch<Action>): DispatchProps => {
    return {
        onAddTeams: (teams) => dispatch(addTeams(teams)),
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(RoutingApp);

import { ModelFyziklaniTeam } from 'FKSDB/Models/ORM/Models/Fyziklani/modelFyziklaniTeam';
import * as React from 'react';
import { connect } from 'react-redux';
import {
    Action,
    Dispatch,
} from 'redux';
import {
    Room,
} from '../../helpers/interfaces';
import { addTeams } from '../actions/teams';
import Form from './form/Index';
import Rooms from './rooms/Index';
import UnRoutedTeams from './unroutedTeams/Index';

interface DispatchProps {
    onAddTeams(teams: ModelFyziklaniTeam[]): void;
}

interface OwnProps {
    teams: ModelFyziklaniTeam[];
    rooms: Room[];
}

class RoutingApp extends React.Component<DispatchProps & OwnProps, Record<string, never>> {

    public componentDidMount() {
        const {onAddTeams, teams} = this.props;
        onAddTeams(teams);
    }

    public render() {
        const {rooms} = this.props;

        return (<>
            <div className="row">
                <div className="col-lg-8" style={{overflowY: 'scroll', maxHeight: '700px'}}>
                    <Rooms rooms={rooms}/>
                </div>
                <div className="col-lg-4" style={{overflowY: 'scroll', maxHeight: '700px'}}>
                    <UnRoutedTeams/>
                </div>
            </div>
            <div>
                <Form/>
            </div>
        </>);
    }
}

const mapDispatchToProps = (dispatch: Dispatch<Action>): DispatchProps => {
    return {
        onAddTeams: (teams) => dispatch(addTeams(teams)),
    };
};

export default connect(null, mapDispatchToProps)(RoutingApp);

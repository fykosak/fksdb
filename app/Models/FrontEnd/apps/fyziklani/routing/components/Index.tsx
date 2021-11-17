import StoreCreator from 'FKSDB/Models/FrontEnd/Loader/StoreCreator';
import { ModelFyziklaniTeam } from 'FKSDB/Models/ORM/Models/Fyziklani/modelFyziklaniTeam';
import * as React from 'react';
import { Room } from '../../helpers/interfaces';
import { app } from '../reducers/';
import App from './App';

interface OwnProps {
    teams: ModelFyziklaniTeam[];
    rooms: Room[];
}

export default class Index extends React.Component<OwnProps, Record<string, never>> {
    public render() {
        const {teams, rooms} = this.props;

        return <StoreCreator app={app}>
            <App teams={teams} rooms={rooms}/>
        </StoreCreator>;
    }
}

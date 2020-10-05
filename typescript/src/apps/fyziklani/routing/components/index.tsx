import StoreCreator from '@shared/components/storeCreator';
import * as React from 'react';
import {
    Room,
    Team,
} from '../../helpers/interfaces';
import { app } from '../reducers/';
import App from './app';

interface OwnProps {
    teams: Team[];
    rooms: Room[];
}

export default class extends React.Component<OwnProps, {}> {
    public render() {
        const {teams, rooms} = this.props;

        return <StoreCreator app={app}>
            <App teams={teams} rooms={rooms}/>
        </StoreCreator>;
    }
}

import { NetteActions } from '@appsCollector/netteActions';
import StoreCreator from '@shared/components/storeCreator';
import * as React from 'react';
import {
    Task,
    Team,
} from '../../helpers/interfaces/';
import { app } from '../reducers/';
import Container from './container';

interface OwnProps {
    tasks: Task[];
    teams: Team[];
    actions: NetteActions;
    availablePoints: number[];
}

export default class TaskCode extends React.Component<OwnProps, {}> {
    public render() {
        const {tasks, teams, actions, availablePoints} = this.props;
        return <StoreCreator app={app}>
            <Container tasks={tasks} teams={teams} actions={actions} availablePoints={availablePoints}/>
        </StoreCreator>;
    }
}

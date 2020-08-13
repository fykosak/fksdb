import { NetteActions } from '@appsCollector/netteActions';
import StoreCreator from '@shared/components/storeCreator';
import * as React from 'react';
import {
    Task,
    Team,
} from '../../helpers/interfaces';
import { app } from '../reducer';
import Container from './container';

interface OwnProps {
    data: {
        availablePoints: number[];
        tasks: Task[];
        teams: Team[];
    };
    actions: NetteActions;

}

export default class Index extends React.Component<OwnProps, {}> {
    public render() {
        const {data, actions} = this.props;
        const {tasks, teams, availablePoints} = data;
        return <StoreCreator app={app}>
            <Container tasks={tasks} teams={teams} actions={actions} availablePoints={availablePoints}/>
        </StoreCreator>;
    }
}

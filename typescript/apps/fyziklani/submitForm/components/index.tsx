import { NetteActions } from '@appsCollector/netteActions';
import StoreCreator from '@shared/components/storeCreator';
import * as React from 'react';
import { app } from '../reducer';
import Container from './container';
import { ModelFyziklaniTeam } from '../../../../../app/Model/ORM/Models/Fyziklani/ModelFyziklaniTeam';
import { ModelFyziklaniTask } from '../../../../../app/Model/ORM/Models/Fyziklani/ModelFyziklaniTask';

interface OwnProps {
    data: {
        availablePoints: number[];
        tasks: ModelFyziklaniTask[];
        teams: ModelFyziklaniTeam[];
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

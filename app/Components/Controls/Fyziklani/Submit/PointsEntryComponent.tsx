import { NetteActions } from '@appsCollector/netteActions';
import { ModelFyziklaniTask } from '@FKSDB/Model/ORM/Models/Fyziklani/ModelFyziklaniTask';
import { ModelFyziklaniTeam } from '@FKSDB/Model/ORM/Models/Fyziklani/ModelFyziklaniTeam';
import StoreCreator from '@shared/components/storeCreator';
import * as React from 'react';
import Container from './Components/Container';
import { app } from './Reducer';

interface OwnProps {
    data: {
        availablePoints: number[];
        tasks: ModelFyziklaniTask[];
        teams: ModelFyziklaniTeam[];
    };
    actions: NetteActions;

}

export default class PointsEntryComponent extends React.Component<OwnProps, {}> {
    public render() {
        const {data, actions} = this.props;
        const {tasks, teams, availablePoints} = data;
        return <StoreCreator app={app}>
            <Container tasks={tasks} teams={teams} actions={actions} availablePoints={availablePoints}/>
        </StoreCreator>;
    }
}

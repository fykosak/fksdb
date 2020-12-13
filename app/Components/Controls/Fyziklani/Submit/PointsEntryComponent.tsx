import { NetteActions } from '@FKSDB/Model/FrontEnd/Loader/netteActions';
import StoreCreator from '@FKSDB/Model/FrontEnd/Loader/StoreCreator';
import { ModelFyziklaniTask } from '@FKSDB/Model/ORM/Models/Fyziklani/modelFyziklaniTask';
import { ModelFyziklaniTeam } from '@FKSDB/Model/ORM/Models/Fyziklani/modelFyziklaniTeam';
import * as React from 'react';
import Container from './Components/Container';
import { app } from './reducer';

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

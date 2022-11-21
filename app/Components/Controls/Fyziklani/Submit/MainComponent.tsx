import { NetteActions } from 'vendor/fykosak/nette-frontend-component/src/NetteActions/netteActions';
import StoreCreator from 'vendor/fykosak/nette-frontend-component/src/Components/StoreCreator';
import { ModelFyziklaniTask } from 'FKSDB/Models/ORM/Models/Fyziklani/modelFyziklaniTask';
import { ModelFyziklaniTeam } from 'FKSDB/Models/ORM/Models/Fyziklani/modelFyziklaniTeam';
import * as React from 'react';
import MainForm from './Components/MainForm';
import { app } from './reducer';

interface OwnProps {
    data: {
        availablePoints: number[] | null;
        tasks: ModelFyziklaniTask[];
        teams: ModelFyziklaniTeam[];
    };
    actions: NetteActions;
}

export default class FOFComponent extends React.Component<OwnProps> {
    public render() {
        const {data, actions} = this.props;
        const {tasks, teams, availablePoints} = data;
        return <StoreCreator app={app}>
            <MainForm tasks={tasks} teams={teams} actions={actions} availablePoints={availablePoints}/>
        </StoreCreator>;
    }
}

import { NetteActions } from 'vendor/fykosak/nette-frontend-component/src/NetteActions/netteActions';
import StoreCreator from 'vendor/fykosak/nette-frontend-component/src/Components/StoreCreator';
import { ModelFyziklaniTask } from 'FKSDB/Models/ORM/Models/Fyziklani/modelFyziklaniTask';
import { ModelFyziklaniTeam } from 'FKSDB/Models/ORM/Models/Fyziklani/modelFyziklaniTeam';
import * as React from 'react';
import CtyrbojForm from './Components/CtyrbojForm';
import FOFForm from './Components/FOFForm';
import { app } from './reducer';

interface OwnProps {
    data: {
        availablePoints: number[];
        tasks: ModelFyziklaniTask[];
        teams: ModelFyziklaniTeam[];
    };
    actions: NetteActions;
    event: 'fof' | 'ctyrboj'
}

export default class FOFComponent extends React.Component<OwnProps> {
    public render() {
        const {data, actions, event} = this.props;
        const {tasks, teams, availablePoints} = data;
        return <StoreCreator app={app}>
            {event === 'fof' ?
                <FOFForm tasks={tasks} teams={teams} actions={actions} availablePoints={availablePoints}/> :
                <CtyrbojForm tasks={tasks} teams={teams} actions={actions} availablePoints={availablePoints}/>
            }
        </StoreCreator>;
    }
}

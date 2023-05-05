import { NetteActions } from 'vendor/fykosak/nette-frontend-component/src/NetteActions/netteActions';
import StoreCreator from 'vendor/fykosak/nette-frontend-component/src/Components/StoreCreator';
import { TaskModel } from 'FKSDB/Models/ORM/Models/Fyziklani/TaskModel';
import { TeamModel } from 'FKSDB/Models/ORM/Models/Fyziklani/TeamModel';
import * as React from 'react';
import MainForm from './Components/MainForm';
import { app } from './reducer';
import { availableLanguage, Translator } from '@translator/translator';
import { TranslatorContext } from '@translator/LangContext';

interface OwnProps {
    data: {
        availablePoints: number[] | null;
        tasks: TaskModel[];
        teams: TeamModel[];
    };
    actions: NetteActions;
    translator: Translator<availableLanguage>;
}

export default class FOFComponent extends React.Component<OwnProps> {
    public render() {
        const {data, actions} = this.props;
        const {tasks, teams, availablePoints} = data;
        return <StoreCreator app={app}>
            <TranslatorContext.Provider value={this.props.translator}>
                <MainForm tasks={tasks} teams={teams} actions={actions} availablePoints={availablePoints}/>
            </TranslatorContext.Provider>
        </StoreCreator>;
    }
}

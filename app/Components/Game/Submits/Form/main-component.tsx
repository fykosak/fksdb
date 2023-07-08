import { NetteActions } from 'vendor/fykosak/nette-frontend-component/src/NetteActions/nette-actions';
import StoreCreator from 'vendor/fykosak/nette-frontend-component/src/Components/store-creator';
import { TaskModel } from 'FKSDB/Models/ORM/Models/Fyziklani/task-model';
import { TeamModel } from 'FKSDB/Models/ORM/Models/Fyziklani/team-model';
import * as React from 'react';
import MainForm from './Components/main-form';
import { app } from './reducer';
import { availableLanguage, Translator } from '@translator/translator';
import { TranslatorContext } from '@translator/context';

interface OwnProps {
    data: {
        availablePoints: number[] | null;
        tasks: TaskModel[];
        teams: TeamModel[];
    };
    actions: NetteActions;
    translator: Translator<availableLanguage>;
}

export default function Component({data: {tasks, teams, availablePoints}, actions, translator}: OwnProps) {
    return <StoreCreator app={app}>
        <TranslatorContext.Provider value={translator}>
            <MainForm tasks={tasks} teams={teams} actions={actions} availablePoints={availablePoints}/>
        </TranslatorContext.Provider>
    </StoreCreator>;
}

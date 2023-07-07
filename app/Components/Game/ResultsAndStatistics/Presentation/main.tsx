import { ResponseData } from 'FKSDB/Components/Game/ResultsAndStatistics/Helpers/Downloader/downloader';
import MainComponent from 'FKSDB/Components/Game/ResultsAndStatistics/Helpers/main-component';
import Toggler from './Components/toggler';
import { NetteActions } from 'vendor/fykosak/nette-frontend-component/src/NetteActions/netteActions';
import * as React from 'react';
import App from './Components/inner-component';
import PositionSwitcher from './Components/position-switcher';
import Setting from './Components/setting';
import { app } from '../reducers/store';
import './style.scss';
import CtyrbojTable from 'FKSDB/Components/Game/ResultsAndStatistics/Presentation/Components/ctyrboj-table';
import { availableLanguage, Translator } from '@translator/translator';

interface OwnProps {
    actions: NetteActions;
    data: ResponseData;
    event: 'fof' | 'ctyrboj';
    translator: Translator<availableLanguage>;
}

export default function Main({actions, data, event, translator}: OwnProps) {
    return <MainComponent
        actions={actions}
        data={data}
        app={app}
        translator={translator}>
        <div className={'game-presentation fixed-top h-100 w-100 game-' + event}>
            <Setting/>
            <Toggler event={event}>
                {event === 'fof'
                    ? <>
                        <App/>
                        <PositionSwitcher/>
                    </>
                    : <CtyrbojTable/>
                }
            </Toggler>
        </div>
    </MainComponent>;
}

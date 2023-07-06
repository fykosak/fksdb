import { ResponseData } from 'FKSDB/Components/Game/ResultsAndStatistics/Helpers/Downloader/downloader';
import MainComponent from 'FKSDB/Components/Game/ResultsAndStatistics/Helpers/main-component';
import { NetteActions } from 'vendor/fykosak/nette-frontend-component/src/NetteActions/netteActions';
import * as React from 'react';
import App from './Components/index';
import { app } from '../reducers/store';
import SingleSelect from 'FKSDB/Components/Game/ResultsAndStatistics/Table/Components/filter-select';
import './results-table.scss';
import { availableLanguage, Translator } from '@translator/translator';

interface OwnProps {
    actions: NetteActions;
    data: ResponseData;
    translator: Translator<availableLanguage>;
}

export default function Main(props: OwnProps) {
    return <MainComponent
        actions={props.actions}
        data={props.data}
        app={app}
        translator={props.translator}>
        <SingleSelect/>
        <App/>
    </MainComponent>;
}

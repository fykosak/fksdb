import { ResponseData } from 'FKSDB/Components/Game/ResultsAndStatistics/Helpers/Downloader/Downloader';
import MainComponent from 'FKSDB/Components/Game/ResultsAndStatistics/Helpers/MainComponent';
import { NetteActions } from 'vendor/fykosak/nette-frontend-component/src/NetteActions/netteActions';
import * as React from 'react';
import App from './Components/Index';
import { app } from '../reducers/store';
import SingleSelect
    from 'FKSDB/Components/Game/ResultsAndStatistics/Table/Components/FilterSelect';
import './results-table.scss';

interface OwnProps {
    actions: NetteActions;
    data: ResponseData;
}

export default class Main extends React.Component<OwnProps> {
    public render() {
        return <MainComponent actions={this.props.actions} data={this.props.data} app={app}>
            <SingleSelect/>
            <App/>
        </MainComponent>;
    }
}
import { ResponseData } from 'FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/Helpers/Downloader/Downloader';
import MainComponent from 'FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/Helpers/MainComponent';
import * as React from 'react';
import { app } from './Reducers';
import App from './StatisticsComponentsRouter';
import { NetteActions } from 'vendor/fykosak/nette-frontend-component/src/NetteActions/netteActions';

interface OwnProps {
    mode: 'correlation' | 'team' | 'task';
    actions: NetteActions;
    data: ResponseData;
}

export default class StatisticsComponent extends React.Component<OwnProps, Record<string, never>> {
    public render() {
        return <MainComponent app={app} data={this.props.data} actions={this.props.actions}>
            <App mode={this.props.mode}/>
        </MainComponent>;
    }
}

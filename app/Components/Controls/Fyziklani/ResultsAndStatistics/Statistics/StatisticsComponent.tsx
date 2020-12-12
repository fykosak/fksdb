import { NetteActions } from '@appsCollector/netteActions';
import { ResponseData } from '@FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/Helpers/Downloader/Downloader';
import * as React from 'react';
import App from './components/app';
import { app } from './reducers';
import MainComponent from '@FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/Helpers/shared/components/mainComponent';

interface OwnProps {
    mode: 'correlation' | 'team' | 'task';
    actions: NetteActions;
    data: ResponseData;
}

export default class StatisticsComponent extends React.Component<OwnProps, {}> {
    public render() {
        return <MainComponent app={app} data={this.props.data} actions={this.props.actions}>
            <App mode={this.props.mode}/>
        </MainComponent>;
    }
}

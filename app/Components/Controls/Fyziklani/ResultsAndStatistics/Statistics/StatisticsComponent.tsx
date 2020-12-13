import { ResponseData } from '@FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/Helpers/Downloader/Downloader';
import MainComponent from '@FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/Helpers/MainComponent';
import { NetteActions } from '@FKSDB/Model/FrontEnd/Loader/netteActions';
import * as React from 'react';
import { app } from './Reducers';
import App from './StatisticsComponentsRouter';

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

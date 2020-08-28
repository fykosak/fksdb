import { ResponseData } from '@apps/fyziklaniResults/downloader/inferfaces';
import MainComponent from '@apps/fyziklaniResults/shared/components/mainComponent';
import { NetteActions } from '@appsCollector/netteActions';
import * as React from 'react';
import App from './components/app';
import { app } from './reducers';

interface OwnProps {
    mode: 'correlation' | 'team' | 'task';
    actions: NetteActions;
    data: ResponseData;
}

export default class StatisticApp extends React.Component<OwnProps, {}> {
    public render() {
        return <MainComponent app={app} data={this.props.data} actions={this.props.actions}>
            <App mode={this.props.mode}/>
        </MainComponent>;
    }
}

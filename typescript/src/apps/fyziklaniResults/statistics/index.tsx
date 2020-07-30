import { NetteActions } from '@appsCollector/netteActions';
import ActionsStoreCreator from '@fetchApi/actionsStoreCreator';
import Powered from '@shared/powered';
import * as React from 'react';
import Downloader from '../downloader/component';
import App from './components/app';
import { app } from './reducers';

interface OwnProps {
    mode: 'correlation' | 'team' | 'task';
    actions: NetteActions;
    data: any;
}

export default class StatisticApp extends React.Component<OwnProps, {}> {
    public render() {
        const accessKey = '@@fyziklani-results';

        const {mode} = this.props;
        return (
            <ActionsStoreCreator actionsMap={{[accessKey]: this.props.actions}} app={app}>
                <div className={'fyziklani-statistics'}>
                    <Downloader accessKey={accessKey}/>
                    <App mode={mode}/>
                </div>
                <Powered/>

            </ActionsStoreCreator>
        );
    }
}

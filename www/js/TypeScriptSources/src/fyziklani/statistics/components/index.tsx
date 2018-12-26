import * as React from 'react';
import { Provider } from 'react-redux';
import {
    applyMiddleware,
    createStore,
} from 'redux';
import logger from 'redux-logger';
import { INetteActions } from '../../../app-collector/';
import { config } from '../../../config';
import Powered from '../../../shared/powered';
import Downloader from '../../helpers/downloader/components/index';
import { app } from '../reducers';
import App from './app';

interface IProps {
    mode: string;
    actions: INetteActions;
}

export default class StatisticApp extends React.Component<IProps, {}> {
    public render() {
        const store = !config.dev ? createStore(app, applyMiddleware(logger)) : createStore(app);
        const accessKey = '@@fyziklani-results';
        const {mode, actions} = this.props;
        return (
            <Provider store={store}>
                <div className={'fyziklani-statistics'}>
                    <Downloader accessKey={accessKey} actions={actions}/>
                    <App mode={mode} accessKey={accessKey}/>
                </div>
                <Powered/>
            </Provider>
        );
    }
}

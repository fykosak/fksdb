import { NetteActions } from '@appsCollector';
import { config } from '@config';
import * as React from 'react';
import { Provider } from 'react-redux';
import {
    applyMiddleware,
    createStore,
} from 'redux';
import logger from 'redux-logger';
import Downloader from '../../helpers/downloader/components/';
import { app } from '../reducers';
import App from './app';

interface OwnProps {
    mode: string;
    actions: NetteActions;
}

export default class Index extends React.Component<OwnProps, {}> {
    public render() {
        const store = config.dev ? createStore(app, applyMiddleware(logger)) : createStore(app);
        const accessKey = '@@fyziklani-results';
        const {mode, actions} = this.props;
        return (
            <Provider store={store}>
                <div className={'fyziklani-results'}>
                    <Downloader accessKey={accessKey} actions={actions}/>
                    <App mode={mode}/>
                </div>
            </Provider>
        );
    }
}

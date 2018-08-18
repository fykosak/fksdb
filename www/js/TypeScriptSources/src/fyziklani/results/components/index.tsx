import * as React from 'react';
import { Provider } from 'react-redux';
import {
    applyMiddleware,
    createStore,
} from 'redux';
import logger from 'redux-logger';
import { config } from '../../../config/';
import Downloader from '../../helpers/downloader/components/index';
import { app } from '../reducers';
import App from './app';

interface IProps {
    mode: string;
}

export default class ResultsApp extends React.Component<IProps, {}> {
    public render() {
        const store = !config.dev ? createStore(app, applyMiddleware(logger)) : createStore(app);
        const accessKey = '@@fyziklani-results';
        const {mode} = this.props;
        return (
            <Provider store={store}>
                <>
                    <Downloader accessKey={accessKey}/>
                    <App mode={mode} accessKey={accessKey}/>
                </>
            </Provider>
        );
    }
}

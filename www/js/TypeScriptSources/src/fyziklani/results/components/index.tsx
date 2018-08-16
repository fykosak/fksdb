import * as React from 'react';
import { Provider } from 'react-redux';
import {
    applyMiddleware,
    createStore,
} from 'redux';
import logger from 'redux-logger';
import { config } from '../../../config/';
import { app } from '../reducers';
import App from './app';
import Downloader from '../../helpers/components/downloader';
// import NavBar from './nav-bar/';

export default class ResultsApp extends React.Component<{}, {}> {
    public render() {
        const store = !config.dev ? createStore(app, applyMiddleware(logger)) : createStore(app);
        const accessKey = '@@fyziklani-results';
        // <NavBar/>
        return (
            <Provider store={store}>
                <>
                    <Downloader accessKey={accessKey}/>
                    <App accessKey={accessKey}/>
                </>
            </Provider>
        );
    }
}

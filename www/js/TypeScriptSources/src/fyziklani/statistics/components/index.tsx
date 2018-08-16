import * as React from 'react';
// import { Provider } from 'react-redux';
// import {
//     applyMiddleware,
//    createStore,
// } from 'redux';
// import logger from 'redux-logger';
// import Powered from '../../../shared/powered';
// import { config } from '../../../config/';
// import Downloader from '../../helpers/components/downloader';
//  import { app } from '../reducers';
// import NavBar from './nav-bar/';

export default class StatisticApp extends React.Component<{}, {}> {
    public render() {
        return null;
        /* const store = config.dev ? createStore(app, applyMiddleware(logger)) : createStore(app);
        const accessKey = '@@fyziklani-results';
        // <NavBar/>
        return (
            <Provider store={store}>
                <>
                    <Downloader accessKey={accessKey}/>

                    <Results basePath={'/'}/>
                    <Powered/>
                </>
            </Provider>
        );*/
    }
}

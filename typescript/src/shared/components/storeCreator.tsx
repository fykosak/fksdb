import { config } from '@config';
import * as React from 'react';
import { Provider } from 'react-redux';
import {
    applyMiddleware,
    createStore, PreloadedState, Reducer,
} from 'redux';
import logger from 'redux-logger';

interface OwnProps {
    preloadState?: PreloadedState<any>;
    app: Reducer<any, any>;
}

export default class StoreCreator extends React.Component<OwnProps, {}> {
    public render() {
        const {preloadState, app} = this.props;
        let store;
        // tslint:disable-next-line:prefer-conditional-expression
        if (preloadState) {
            store = config.dev ? createStore(app, preloadState, applyMiddleware(logger)) : createStore(app, preloadState);
        } else {
            store = config.dev ? createStore(app, applyMiddleware(logger)) : createStore(app);
        }

        return <Provider store={store}>
            {this.props.children}
        </Provider>;
    }
}

import { config } from 'FKSDB/config/config';
import * as React from 'react';
import { Provider } from 'react-redux';
import {
    applyMiddleware,
    createStore, Reducer,
} from 'redux';
import logger from 'redux-logger';

interface OwnProps {
    app: Reducer<any, any>;
}

export default class StoreCreator extends React.Component<OwnProps, Record<string, never>> {
    public render() {
        const {app} = this.props;
        const store = config.dev ? createStore(app, applyMiddleware(logger)) : createStore(app);

        return <Provider store={store}>
            {this.props.children}
        </Provider>;
    }
}

import * as React from "react";

import { Provider } from 'react-redux';
import {
    applyMiddleware,
    createStore,
} from 'redux';
import logger from 'redux-logger';
import {
    IUploadDataItem,
} from '../../../shared/interfaces';
import { app } from '../../reducers';
import App from './app';

interface IProps {
    data: IUploadDataItem;
}

export default class Index extends React.Component<IProps, {}> {

    public render() {
        // const store = config.dev ? createStore(app, applyMiddleware(logger)) : createStore(app);
        const store = createStore(app, applyMiddleware(logger));
        return <Provider store={store}><App data={this.props.data}/></Provider>;
    }
}

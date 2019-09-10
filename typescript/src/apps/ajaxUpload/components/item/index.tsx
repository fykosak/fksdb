import { NetteActions } from '@appsCollector';
import * as React from "react";
import { Provider } from 'react-redux';
import {
    applyMiddleware,
    createStore,
} from 'redux';
import logger from 'redux-logger';
import { UploadDataItem } from '../../middleware/uploadDataItem';
import { app } from '../../reducers';
import App from './app';

interface IProps {
    data: UploadDataItem;
    actions: NetteActions;
}

export default class Index extends React.Component<IProps, {}> {

    public render() {
        const store = /*config.dev ? */createStore(app, applyMiddleware(logger))/* : createStore(app)*/;
        return <Provider store={store}>
            <App data={this.props.data} actions={this.props.actions}/>
        </Provider>;
    }
}

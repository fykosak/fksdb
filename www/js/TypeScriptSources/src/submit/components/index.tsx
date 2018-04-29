import * as React from "react";
import * as ReactDOM from "react-dom";

import { Provider } from 'react-redux';
import {
    applyMiddleware,
    createStore,
} from 'redux';
import logger from 'redux-logger';
import { config } from '../../config';
import { IUploadData } from '../../shared/interfaces';
import { app } from '../reducers';
import App from './app';

const el = document.getElementById('ajax-submit-form');

interface IProps {
    data: IUploadData;
}

class Index extends React.Component<IProps, {}> {

    public render() {
        // const store = config.dev ? createStore(app, applyMiddleware(logger)) : createStore(app);
        const store = createStore(app, applyMiddleware(logger));
        return <Provider store={store}><App data={this.props.data}/></Provider>;
    }
}

if (el) {
    const data = JSON.parse(el.getAttribute('data-upload-data'));
    ReactDOM.render(<Index data={data}/>, el);
}

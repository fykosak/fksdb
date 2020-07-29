import UploadContainer from '@apps/ajaxUpload/components/item/container';
import { NetteActions } from '@appsCollector';
import { config } from '@config';
import * as React from 'react';
import { Provider } from 'react-redux';
import {
    applyMiddleware,
    createStore,
} from 'redux';
import logger from 'redux-logger';
import { UploadDataItem } from '../../middleware/uploadDataItem';
import { app } from '../../reducers';

interface IProps {
    data: UploadDataItem;
    actions: NetteActions;
}

export default class Index extends React.Component<IProps, {}> {

    public render() {
        const accessKey = '@@submit-api/' + this.props.data.taskId;
        const state = {
            fetchApi: {
                [accessKey]: {
                    actions: this.props.actions,
                    error: null,
                    messages: [],
                    submitting: false,
                },
            },
            uploadData: {
                actions: this.props.actions,
                submit: this.props.data,
            },
        };
        const store = config.dev ?
            createStore(app, state, applyMiddleware(logger)) :
            createStore(app, state);

        return <Provider store={store}>
            <UploadContainer accessKey={accessKey}/>
        </Provider>;
    }
}

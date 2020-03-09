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
import UploadContainer from '@apps/ajaxUpload/components/item/container';

interface IProps {
    data: UploadDataItem;
    actions: NetteActions;
}

export default class Index extends React.Component<IProps, {}> {

    public render() {
        const store = config.dev ?
            createStore(app, {
                uploadData: {
                    ...this.props.data,
                },
            }, applyMiddleware(logger)) :
            createStore(app, {
                uploadData: {
                    ...this.props.data,
                },
            });
        const accessKey = '@@submit-api/' + this.props.data.taskId;
        return <Provider store={store}>
            <UploadContainer actions={this.props.actions} accessKey={accessKey}/>
        </Provider>;
    }
}

import { NetteActions } from '@appsCollector';
import { lang } from '@i18n/i18n';
import * as React from 'react';
import { UploadDataItem } from '../middleware/uploadDataItem';
import ItemIndex from './item';

interface Props {
    data: {
        [taskId: number]: UploadDataItem;
    };
    actions: NetteActions;
}

export default class Index extends React.Component<Props, {}> {

    public render() {
        const {data, actions} = this.props;
        const boxes = [];
        for (const taskId in data) {
            if (data.hasOwnProperty(taskId)) {
                boxes.push(<ItemIndex actions={actions} key={taskId} data={data[taskId]}/>);
            }
        }
        if (boxes.length) {
            return <div className="row">{boxes}</div>;
        } else {
            return <div className="alert alert-info">{lang.getText('No tasks available')}</div>;
        }
    }
}

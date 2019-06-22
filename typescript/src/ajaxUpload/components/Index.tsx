import * as React from 'react';
import {
    UploadData,
    UploadDataItem,
} from '../middleware/UploadDataItem';
import ItemIndex from './item/Index';
import { NetteActions } from '../../app-collector';

interface Props {
    data: UploadData;
    actions: NetteActions;
}

export default class Index extends React.Component<Props, {}> {

    public render() {
        const boxes = [];
        for (const taskId in this.props.data) {
            if (this.props.data.hasOwnProperty(taskId)) {
                const data: UploadDataItem = this.props.data[taskId];
                boxes.push(<ItemIndex actions={this.props.actions} key={taskId} data={data}/>);
            }
        }
        return <div className="row">{boxes}</div>;
    }
}

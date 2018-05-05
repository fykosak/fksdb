import * as React from "react";
import * as ReactDOM from "react-dom";
import ItemIndex from './upload-item/index';

import {
    IUploadData,
    IUploadDataItem,
} from '../../shared/interfaces';

const el = document.getElementById('ajax-submit-form');

interface IProps {
    data: IUploadData;
}

class Index extends React.Component<IProps, {}> {

    public render() {
        const boxes = [];
        for (const taskId in this.props.data) {
            if (this.props.data.hasOwnProperty(taskId)) {
                const data: IUploadDataItem = this.props.data[taskId];
                boxes.push(<ItemIndex key={taskId} data={data}/>);
            }
        }
        return <div className="row">{boxes}</div>;
    }
}

if (el) {
    const data = JSON.parse(el.getAttribute('data-upload-data'));
    ReactDOM.render(<Index data={data}/>, el);
}

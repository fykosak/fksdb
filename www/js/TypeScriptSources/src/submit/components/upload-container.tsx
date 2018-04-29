import * as React from 'react';
import { connect } from 'react-redux';
import Card from '../../shared/components/card';
import {
    IUploadData,
    IUploadDataItem,
} from '../../shared/interfaces';
import UploadForm from './upload-form';
import UploadedFile from './uploaded-file';

interface IState {
    data?: IUploadData;
}

class UploadContainer extends React.Component<IState, {}> {

    public render() {
        const boxes = [];
        for (const taskId in this.props.data) {
            if (this.props.data.hasOwnProperty(taskId)) {
                const data: IUploadDataItem = this.props.data[taskId];
                boxes.push(<div className="col-6 mb-3" key={taskId}>
                    <Card headline={data.name + ' - ' + data.deadline} level={'info'}>
                        {data.submitId ? (
                                <UploadedFile name={data.name} href={data.href} submitId={data.submitId}/>) :
                            <UploadForm data={data}/>}

                    </Card>
                </div>);
            }
        }
        return <div className="row">{boxes}</div>;

    }
}

const mapStateToProps = (state): IState => {
    return {
        data: {...state.uploadData},
    };
};
const mapDispatchToProps = (): IState => {
    return {};
};

export default connect(mapStateToProps, mapDispatchToProps)(UploadContainer);

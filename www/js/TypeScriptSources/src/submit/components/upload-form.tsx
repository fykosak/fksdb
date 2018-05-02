import * as React from 'react';

import { connect } from 'react-redux';
import { IUploadDataItem } from '../../shared/interfaces';
import { newDataArrived } from '../actions/upload-data';
import {
    submitFail,
    submitStart,
    submitSuccess,
} from '../../shared/actions/submit';
import {
    dragEnd,
    dragStart,
} from '../../shared/actions/dragndrop';
import { IStore } from '../reducers';
import {
    handleFileUpload,
    uploadFile,
} from '../middleware/upload';

interface IProps {
    data: IUploadDataItem;
}

interface IState {
    onNewDataArrived?: (data: IUploadDataItem) => void;
    isSubmitting?: boolean;
    onSubmitFail?: (error) => void;
    onSubmitStart?: () => void;
    onSubmitSuccess?: (data) => void;
    onDragStart?: () => void;
    onDragEnd?: () => void;
    dragged?: boolean;
}

class UploadForm extends React.Component<IProps & IState, { dragged: boolean }> {

    constructor() {
        super();
        this.state = {dragged: false};
    }

    public render() {
        const handleDragEnd = (event) => {
            event.preventDefault();
            this.props.onDragEnd();
        };
        const handleDragStart = (event) => {
            event.preventDefault();
            this.props.onDragStart();
        };
        const onUploadFile = (event) => {
            handleDragEnd(event);

            const data: FileList = event.dataTransfer.files;
            const {onSubmitSuccess, onNewDataArrived, onSubmitFail, onSubmitStart} = this.props;

            onSubmitStart();
            handleFileUpload(data, (formData) => {
                return uploadFile(formData,
                    (d) => {
                        onSubmitSuccess(d);
                        onNewDataArrived(d.data);
                    },
                    onSubmitFail);
            }, this.props.data.taskId);
        };

        return <div className={'drop-input' + (this.state.dragged ? ' dragged' : '')}
                    onDrop={onUploadFile}
                    onDragOver={handleDragStart}
                    onDragEnter={handleDragStart}
                    onDragLeave={handleDragEnd}
                    onDragEnd={handleDragEnd}
        >
            <div className="drop-input-inner">
                <div className="text-center">
                    <span className="display-1 d-block"><i className="fa fa-download"/></span>
                    <span className="d-block"> <strong>Choose a file</strong>
                        <span className="box__dragndrop"> or drag it here</span>.</span>
                </div>
            </div>
        </div>;
    }

}

const mapStateToProps = (state: IStore): IState => {
    return {
        dragged: state.dragNDrop.dragged,
    };
};
const mapDispatchToProps = (dispatch): IState => {
    return {
        onDragEnd: () => dispatch(dragEnd()),
        onDragStart: () => dispatch(dragStart()),
        onNewDataArrived: (data: IUploadDataItem) => dispatch(newDataArrived(data)),
        onSubmitFail: (error) => dispatch(submitFail(error)),
        onSubmitStart: () => dispatch(submitStart()),
        onSubmitSuccess: (data) => dispatch(submitSuccess(data)),
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(UploadForm);

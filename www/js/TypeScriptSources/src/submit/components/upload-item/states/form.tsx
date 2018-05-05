import * as React from 'react';

import { connect } from 'react-redux';
import {
    dragEnd,
    dragStart,
    dropItem,
} from '../../../../shared/actions/dragndrop';
import {
    submitFail,
    submitStart,
    submitSuccess,
} from '../../../../shared/actions/submit';
import {
    IReciveData,
    IUploadDataItem,
} from '../../../../shared/interfaces';
import { newDataArrived } from '../../../actions/upload-data';
import {
    handleFileUpload,
} from '../../../middleware/upload';
import { IStore } from '../../../reducers/index';
import { uploadFile } from '../../../../shared/helpers/fetch';

interface IProps {
    data: IUploadDataItem;
}

interface IState {
    onDropItem?: (item: any) => void,
    isSubmitting?: boolean;
    onSubmitFail?: (error) => void;
    onSubmitStart?: () => void;
    onSubmitSuccess?: (data) => void;
    onDragStart?: () => void;
    onDragEnd?: () => void;
    dragged?: boolean;
}

class Form extends React.Component<IProps & IState, {}> {

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
            const {onSubmitSuccess, onSubmitFail, onSubmitStart} = this.props;

            onSubmitStart();
            handleFileUpload(data, (formData) => {
                return uploadFile<FormData, IReciveData<any>>(formData,
                    (d) => {
                        onSubmitSuccess(d);
                    },
                    onSubmitFail);
            }, this.props.data.taskId);
        };
        const {dragged} = this.props;

        return <div className={'drop-input' + (dragged ? ' dragged' : '')}
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
                        <span> or drag it here</span>.</span>
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
        onDropItem: (item) => dispatch(dropItem<any>(item)),
        onSubmitFail: (error) => dispatch(submitFail(error)),
        onSubmitStart: () => dispatch(submitStart()),
        onSubmitSuccess: (data) => dispatch(submitSuccess<IUploadDataItem>(data)),
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(Form);

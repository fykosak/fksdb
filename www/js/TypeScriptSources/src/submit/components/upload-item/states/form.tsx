import * as React from 'react';

import { connect } from 'react-redux';
import {
    dragEnd,
    dragStart,
    dropItem,
} from '../../../../shared/actions/dragndrop';
import { addError } from '../../../../shared/actions/error-logger';
import {
    submitFail,
    submitStart,
    submitSuccess,
} from '../../../../shared/actions/submit';
import { uploadFile } from '../../../../shared/helpers/fetch';
import {
    IReciveData,
    IUploadDataItem,
} from '../../../../shared/interfaces';
import {
    handleFileUpload,
} from '../../../middleware/upload';
import { IStore } from '../../../reducers';

interface IProps {
    data: IUploadDataItem;
}

interface IState {
    onDropItem?: (item: any) => void;
    isSubmitting?: boolean;
    onSubmitFail?: (error) => void;
    onSubmitStart?: () => void;
    onSubmitSuccess?: (data) => void;
    onDragStart?: () => void;
    onDragEnd?: () => void;
    dragged?: boolean;
    onAddError?: (error) => void;
}

class Form extends React.Component<IProps & IState, {}> {

    public render() {
        const {onDropItem, onSubmitSuccess, onDragEnd, onDragStart, onSubmitFail, onSubmitStart, onAddError} = this.props;
        const handleDragEnd = (event) => {
            event.preventDefault();
            onDragEnd();
        };
        const handleDragStart = (event) => {
            event.preventDefault();
            onDragStart();
        };

        const onUploadFile = (event) => {
            event.preventDefault();
            const data: FileList = event.dataTransfer.files;
            onDropItem(data);

            const formData = handleFileUpload(data, this.props.data.taskId, onAddError);
            if (formData) {
                onSubmitStart();
                return uploadFile<FormData, IReciveData<any>>(formData, onSubmitSuccess, onSubmitFail);
            }
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
                    <span className="d-block">
                        <span>Drag file here</span>.</span>
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
        onAddError: (error) => dispatch(addError(error)),
        onDragEnd: () => dispatch(dragEnd()),
        onDragStart: () => dispatch(dragStart()),
        onDropItem: (item) => dispatch(dropItem<any>(item)),
        onSubmitFail: (error) => dispatch(submitFail(error)),
        onSubmitStart: () => dispatch(submitStart()),
        onSubmitSuccess: (data) => dispatch(submitSuccess<IUploadDataItem>(data)),
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(Form);

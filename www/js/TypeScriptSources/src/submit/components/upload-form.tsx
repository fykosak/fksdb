import * as React from 'react';

import { connect } from 'react-redux';
import { IUploadDataItem } from '../../shared/interfaces';
import { newDataArrived } from '../actions/upload-data';
import {
    submitFail,
    submitStart,
    submitSuccess,
} from '../../shared/actions/submit';

interface IProps {
    data: IUploadDataItem;
}

interface IState {
    onNewDataArrived?: (data: IUploadDataItem) => void;
    isSubmitting?: boolean;
    onSubmitFail?: (error) => void;
    onSubmitStart?: () => void;
    onSubmitSuccess?: (data) => void;
}

class UploadForm extends React.Component<IProps & IState, { dragged: boolean }> {

    constructor() {
        super();
        this.state = {dragged: false};
    }

    public render() {
        const dragEnd = (event) => {
            event.preventDefault();
            this.setState({dragged: false});
        };
        const dragStart = (event) => {
            event.preventDefault();
            this.setState({dragged: true});
        };
        const onUploadFile = (event) => {
            dragEnd(event);

            const data2 = event.dataTransfer.files;

            // if (form && form instanceof HTMLFormElement) {
            const formData = new FormData();
            for (const i in data2) {
                if (data2.hasOwnProperty(i)) {
                    formData.append('task' + this.props.data.taskId, data2[i]);
                }
            }
            formData.set('act', 'upload');
            const {onSubmitSuccess, onNewDataArrived, onSubmitFail, onSubmitStart} = this.props;
            onSubmitStart();
            uploadFile(formData, (data) => {
                onSubmitSuccess(data);
                onNewDataArrived(data.data);
            }, (e) => {
                onSubmitFail(e);
            });
            // }
        };

        return <div className={'drop-input' + (this.state.dragged ? ' dragged' : '')}
                    onDrop={onUploadFile}
                    onDragOver={dragStart}
                    onDragEnter={dragStart}
                    onDragLeave={dragEnd}
                    onDragEnd={dragEnd}
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

const mapStateToProps = (state): IState => {
    return {
        // isSubmitting: state.submit.isSubmitting,
    };
};
const mapDispatchToProps = (dispatch): IState => {
    return {
        onNewDataArrived: (data: IUploadDataItem) => dispatch(newDataArrived(data)),
        onSubmitFail: (error) => dispatch(submitFail(error)),
        onSubmitStart: () => dispatch(submitStart()),
        onSubmitSuccess: (data) => dispatch(submitSuccess(data)),
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(UploadForm);

const uploadFile = (formData: FormData, success, error) => {
    return new Promise((resolve, reject) => {
        $.ajax({
            cache: false,
            complete: () => {
                //    $form.removeClass('is-uploading');
            },
            contentType: false,
            data: formData,
            dataType: 'json',
            error: (e) => {
                reject(e);
                error(e);
            },
            processData: false,
            success: (data) => {
                resolve(data);
                console.log(data);
                success(data);
            },
            type: 'POST',
            url: '#',
        });
    });
};

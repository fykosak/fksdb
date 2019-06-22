import * as React from 'react';
import { connect } from 'react-redux';
import { dispatchUploadFile } from '../../../../fetch-api/middleware/fetch';
import {
    addError,
    dragEnd,
    dragStart,
    dropItem,
} from '../../../actions/';

import {
    Action,
    Dispatch,
} from 'redux';
import { NetteActions } from '../../../../app-collector';
import { handleFileUpload } from '../../../middleware/upload';
import { UploadDataItem } from '../../../middleware/UploadDataItem';
import { Store } from '../../../reducers';

interface Props {
    actions: NetteActions;
    data: UploadDataItem;
    accessKey: string;
}

interface State {
    isSubmitting?: boolean;

    dragged?: boolean;

    onDropItem?(item: any): void;

    onFileUpload?(data): void;

    onDragStart?(): void;

    onDragEnd?(): void;

    onAddError?(error): void;
}

class Form extends React.Component<Props & State, {}> {

    public render() {
        const {onDropItem, onDragEnd, onDragStart, onFileUpload, onAddError} = this.props;
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
                onFileUpload(formData);
            }
        };

        const onFileInputChanged = (event) => {
            event.preventDefault();
            const data: FileList = event.target.files;
            const formData = handleFileUpload(data, this.props.data.taskId, onAddError);
            if (formData) {
                onFileUpload(formData);
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
                    <input type={'file'} onChange={onFileInputChanged} accept="application/pdf"/>
                </div>
            </div>
        </div>;
    }

}

const mapStateToProps = (state: Store): State => {
    return {
        dragged: state.dragNDrop.dragged,
    };
};
const mapDispatchToProps = (dispatch: Dispatch<Action<string>>, ownProps: Props): State => {
    return {
        onAddError: (error) => dispatch(addError(error)),
        onDragEnd: () => dispatch(dragEnd()),
        onDragStart: () => dispatch(dragStart()),
        onDropItem: (item) => dispatch(dropItem<any>(item)),
        onFileUpload: (values) => dispatchUploadFile(ownProps.accessKey, dispatch, values, () => null, () => null, ownProps.actions.upload),
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(Form);

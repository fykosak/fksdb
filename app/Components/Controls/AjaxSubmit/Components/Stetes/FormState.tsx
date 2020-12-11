import * as React from 'react';
import { connect } from 'react-redux';
import { Action, Dispatch, } from 'redux';
import { handleFileUpload, Submit } from '../../Middleware/';
import { Store } from '../../Reducers';
import { NetteActions } from '../../../../../../typescript/appsCollector/netteActions';
import { addError } from '../../Actions';
import { dragEnd, dragStart, dropItem } from '../../../../../../typescript/shared/dragndrop';
import { dispatchFetch } from '../../../../../../typescript/fetchApi/netteFetch';

interface OwnProps {
    submit: Submit;
}

interface DispatchProps {

    onDropItem(item: any): void;

    onFileUpload(data: FormData, url: string): void;

    onDragStart(): void;

    onDragEnd(): void;

    onAddError(error): void;
}

interface StateProps {
    dragged: boolean;
    actions: NetteActions;
}

class FormState extends React.Component<OwnProps & StateProps & DispatchProps, {}> {

    public render() {
        const {dragged} = this.props;

        return <div className={'drop-input' + (dragged ? ' dragged' : '')}
                    onDrop={(event) => {
                        this.onUploadFile(event);
                    }}
                    onDragOver={(event) => {
                        this.handleDragStart(event);
                    }}
                    onDragEnter={(event) => {
                        this.handleDragStart(event);
                    }}
                    onDragLeave={(event) => {
                        this.handleDragEnd(event);
                    }}
                    onDragEnd={(event) => {
                        this.handleDragEnd(event);
                    }}
        >
            <div className="drop-input-inner">
                <div className="text-center">
                    <span className="display-1 d-block"><i className="fa fa-download"/></span>
                    <span className="d-block">
                        <span>Drag file here</span>.</span>
                    <input type={'file'} onChange={(event) => {
                        this.onFileInputChanged(event);
                    }} accept="application/pdf"/>
                </div>
            </div>
        </div>;
    }

    private handleDragEnd(event: React.DragEvent<HTMLDivElement>): void {
        event.preventDefault();
        this.props.onDragEnd();
    }

    private handleDragStart(event: React.DragEvent<HTMLDivElement>): void {
        event.preventDefault();
        this.props.onDragStart();
    }

    private onUploadFile(event: React.DragEvent<HTMLDivElement>): void {
        event.preventDefault();
        const data: FileList = event.dataTransfer.files;
        this.props.onDropItem(data);
        this.handleFile(data);
    }

    private onFileInputChanged(event: React.ChangeEvent<HTMLInputElement>): void {
        event.preventDefault();
        this.handleFile(event.target.files);
    }

    private handleFile(fileList: FileList): void {
        const formData = handleFileUpload(fileList, this.props.onAddError);
        if (formData) {
            this.props.onFileUpload(formData, this.props.actions.getAction('upload'));
        }
    }
}

const mapStateToProps = (state: Store): StateProps => {
    return {
        actions: state.fetchApi.actions,
        dragged: state.dragNDrop.dragged,
    };
};
const mapDispatchToProps = (dispatch: Dispatch<Action<string>>): DispatchProps => {
    return {
        onAddError: (error) => dispatch(addError(error)),
        onDragEnd: () => dispatch(dragEnd()),
        onDragStart: () => dispatch(dragStart()),
        onDropItem: (item) => dispatch(dropItem<any>(item)),
        onFileUpload: (values, url: string) => dispatchFetch(url, dispatch, values),
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(FormState);

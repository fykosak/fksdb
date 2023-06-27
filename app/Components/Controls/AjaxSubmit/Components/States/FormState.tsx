import { dispatchNetteFetch } from 'vendor/fykosak/nette-frontend-component/src/fetch/redux/netteFetch';
import { NetteActions } from 'vendor/fykosak/nette-frontend-component/src/NetteActions/netteActions';
import { dragEnd, dragStart, dropItem } from 'FKSDB/Models/FrontEnd/shared/dragndrop';
import * as React from 'react';
import { connect } from 'react-redux';
import { Action, Dispatch } from 'redux';
import { addError } from '../../actions';
import { handleFileUpload } from '../../middleware';
import { Store } from '../../Reducers';
import { Message } from 'vendor/fykosak/nette-frontend-component/src/Responses/response';
import { TranslatorContext } from '@translator/LangContext';

interface DispatchProps {

    onDropItem(item: FileList): void;

    onFileUpload(data: FormData, url: string): void;

    onDragStart(): void;

    onDragEnd(): void;

    onAddError(error: Message): void;
}

interface StateProps {
    dragged: boolean;
    actions: NetteActions;
}

class FormState extends React.Component<StateProps & DispatchProps, never> {
    static contextType = TranslatorContext;

    public render() {
        const translator = this.context;
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
            <div className="drop-input-inner p-3">
                <div className="text-center">
                    <span className="display-1 d-block"><i className="fa fa-download"/></span>
                    <span className="d-block p-1">
                        <span>{translator.getText('Drag file here.')}</span>
                    </span>
                    <input
                        className="form-control"
                        type="file"
                        onChange={(event) => {
                            this.onFileInputChanged(event);
                        }}
                        accept="application/pdf"
                    />
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
        const translator = this.context;
        const formData = handleFileUpload(fileList, this.props.onAddError, translator);
        if (formData) {
            this.props.onFileUpload(formData, this.props.actions.getAction('upload'));
        }
    }
}

const mapStateToProps = (state: Store): StateProps => {
    return {
        actions: state.fetch.actions,
        dragged: state.dragNDrop.dragged,
    };
};
const mapDispatchToProps = (dispatch: Dispatch<Action<string>>): DispatchProps => {
    return {
        onAddError: (error) => dispatch(addError(error)),
        onDragEnd: () => dispatch(dragEnd()),
        onDragStart: () => dispatch(dragStart()),
        onDropItem: (item) => dispatch(dropItem<FileList>(item)),
        onFileUpload: (values, url: string) => dispatchNetteFetch(url, dispatch, values),
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(FormState);

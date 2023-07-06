import { dispatchNetteFetch } from 'vendor/fykosak/nette-frontend-component/src/fetch/redux/netteFetch';
import { NetteActions } from 'vendor/fykosak/nette-frontend-component/src/NetteActions/netteActions';
import { dragEnd, dragStart, dropItem } from 'FKSDB/Models/FrontEnd/shared/dragndrop';
import * as React from 'react';
import { useContext } from 'react';
import { connect } from 'react-redux';
import { Action, Dispatch } from 'redux';
import { addError } from '../../actions';
import { handleFileUpload } from '../../middleware';
import { Store } from '../../Reducers';
import { Message } from 'vendor/fykosak/nette-frontend-component/src/Responses/response';
import { TranslatorContext } from '@translator/context';

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

function FormState({
                       dragged,
                       onDragEnd,
                       onDragStart,
                       onAddError,
                       actions,
                       onDropItem,
                       onFileUpload,
                   }: StateProps & DispatchProps) {
    const translator = useContext(TranslatorContext);

    const handleDragEnd = (event: React.DragEvent<HTMLDivElement>): void => {
        event.preventDefault();
        onDragEnd();
    }

    const handleDragStart = (event: React.DragEvent<HTMLDivElement>): void => {
        event.preventDefault();
        onDragStart();
    }

    const onUploadFile = (event: React.DragEvent<HTMLDivElement>): void => {
        event.preventDefault();
        const data: FileList = event.dataTransfer.files;
        onDropItem(data);
        handleFile(data);
    }

    const onFileInputChanged = (event: React.ChangeEvent<HTMLInputElement>): void => {
        event.preventDefault();
        handleFile(event.target.files);
    }

    const handleFile = (fileList: FileList): void => {
        const formData = handleFileUpload(fileList, onAddError, translator);
        if (formData) {
            onFileUpload(formData, actions.getAction('upload'));
        }
    }
    return <div className={'drop-input' + (dragged ? ' dragged' : '')}
                onDrop={(event) => {
                    onUploadFile(event);
                }}
                onDragOver={(event) => {
                    handleDragStart(event);
                }}
                onDragEnter={(event) => {
                    handleDragStart(event);
                }}
                onDragLeave={(event) => {
                    handleDragEnd(event);
                }}
                onDragEnd={(event) => {
                    handleDragEnd(event);
                }}
    >
        <div className="drop-input-inner p-3">
            <div className="text-center">
                <span className="display-1 d-block"><i className="fas fa-download"/></span>
                <span className="d-block p-1">
                        <span>{translator.getText('Drop pdf file here.')}</span>
                    </span>
                <input
                    className="form-control"
                    type="file"
                    onChange={(event) => {
                        onFileInputChanged(event);
                    }}
                    accept="application/pdf"
                />
            </div>
        </div>
    </div>;
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

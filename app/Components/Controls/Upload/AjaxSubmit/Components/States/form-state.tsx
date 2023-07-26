import { dispatchNetteFetch } from 'vendor/fykosak/nette-frontend-component/src/fetch/redux/nette-fetch';
import { ACTION_DRAG_END, ACTION_DRAG_START, dropItem } from 'FKSDB/Models/FrontEnd/shared/dragndrop';
import * as React from 'react';
import { useContext } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { addError } from '../../actions';
import { handleFileUpload } from '../../middleware';
import { Store } from '../../Reducers';
import { TranslatorContext } from '@translator/context';

export default function FormState() {
    const translator = useContext(TranslatorContext);
    const dispatch = useDispatch();
    const actions = useSelector((state: Store) => state.fetch.actions);
    const dragged = useSelector((state: Store) => state.dragNDrop);

    const handleDragEnd = (event: React.DragEvent<HTMLDivElement>): void => {
        event.preventDefault();
        dispatch({type: ACTION_DRAG_END});
    }

    const handleDragStart = (event: React.DragEvent<HTMLDivElement>): void => {
        event.preventDefault();
        dispatch({type: ACTION_DRAG_START});
    }

    const onUploadFile = (event: React.DragEvent<HTMLDivElement>): void => {
        event.preventDefault();
        const data: FileList = event.dataTransfer.files;
        dispatch(dropItem<FileList>(data))
        handleFile(data);
    }

    const onFileInputChanged = (event: React.ChangeEvent<HTMLInputElement>): void => {
        event.preventDefault();
        handleFile(event.target.files);
    }

    const handleFile = (fileList: FileList): void => {
        const formData = handleFileUpload(fileList, (error) => dispatch(addError(error)), translator);
        if (formData) {
            dispatchNetteFetch(actions.getAction('upload'), dispatch, formData)
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

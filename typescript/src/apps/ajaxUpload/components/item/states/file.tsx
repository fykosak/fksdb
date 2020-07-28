import { UploadDataItem } from '@apps/ajaxUpload/middleware/uploadDataItem';
import { NetteActions } from '@appsCollector';
import { lang } from '@i18n/i18n';
import * as React from 'react';
import { connect } from 'react-redux';
import {
    Action,
    Dispatch,
} from 'redux';
import { handleSubmit } from '../../../actions/uploadData';

interface OwnProps {
    submit: UploadDataItem;
    actions: NetteActions;
    accessKey: string;
}

interface DispatchProps {
    onDeleteFile(): void;

    onDownloadFile(): void;
}

class File extends React.Component<OwnProps & DispatchProps, {}> {

    public render() {
        return <div className="uploaded-file">
            <button aria-hidden="true" className="pull-right btn btn-warning" title={lang.getText('Revoke')}
                    onClick={() => {
                        if (window.confirm(lang.getText('Remove submit?'))) {
                            this.props.onDeleteFile();
                        }
                    }}>&times;</button>
            <div className="text-center p-2">
                <a onClick={() => {
                    this.props.onDownloadFile();
                }} href="#">
                    <span className="display-1 w-100"><i className="fa fa-file-pdf-o"/></span>
                    <span className="d-block">{this.props.submit.name}</span>
                </a>
            </div>
        </div>;
    }
}

const mapDispatchToProps = (dispatch: Dispatch<Action<string>>, ownProps: OwnProps): DispatchProps => {
    const {accessKey, submit: {submitId}} = ownProps;
    return {
        onDeleteFile: () => handleSubmit(dispatch, accessKey, submitId, ownProps.actions.getAction('revoke')),
        onDownloadFile: () => handleSubmit(dispatch, accessKey, submitId, ownProps.actions.getAction('download')),
    };
};

export default connect(null, mapDispatchToProps)(File);

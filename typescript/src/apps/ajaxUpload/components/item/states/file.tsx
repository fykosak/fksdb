import { UploadDataItem } from '@apps/ajaxUpload/middleware/uploadDataItem';
import { NetteActions } from '@appsCollector/netteActions';
import { lang } from '@i18n/i18n';
import * as React from 'react';
import { connect } from 'react-redux';
import {
    Action,
    Dispatch,
} from 'redux';
import { deleteUploadedFile } from '../../../actions/uploadData';

interface OwnProps {
    submit: UploadDataItem;
    actions: NetteActions;
    accessKey: string;
}

interface DispatchProps {
    onDeleteFile(accessKey: string, submitId: number): void;

    //  onDownloadFile(accessKey: string, submitId: number): void;
}

class File extends React.Component<OwnProps & DispatchProps, {}> {

    public render() {
        return <div className="uploaded-file">
            <button aria-hidden="true" className="pull-right btn btn-warning" title={lang.getText('Revoke')}
                    onClick={() => {
                        if (window.confirm(lang.getText('Remove submit?'))) {
                            this.props.onDeleteFile(this.props.accessKey, this.props.submit.submitId);
                        }
                    }}>&times;</button>
            <div className="text-center p-2">
                <span className="display-1 w-100"><i className="fa fa-file-pdf-o"/></span>
                <span className="d-block">{this.props.submit.name}</span>
            </div>

        </div>;
    }
}

/*
              <a href="#" onClick={() => {
                    this.props.onDownloadFile(this.props.accessKey, this.props.submit.submitId);
                }}>
                    </a>
 */

const mapDispatchToProps = (dispatch: Dispatch<Action<string>>, ownProps: OwnProps): DispatchProps => {
    return {
        onDeleteFile: (accessKey, submitId) => deleteUploadedFile(dispatch, accessKey, submitId, ownProps.actions.getAction('revoke')),
        // onDownloadFile: (accessKey, submitId) => downloadUploadedFile(dispatch, accessKey, submitId, ownProps.actions.getAction('download')),
    };
};

export default connect(null, mapDispatchToProps)(File);

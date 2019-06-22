import * as React from 'react';
import { connect } from 'react-redux';
import { deleteUploadedFile } from '../../../actions/uploadData';
import { lang } from '../../../../i18n/i18n';
import { NetteActions } from '../../../../app-collector';
import {
    Action,
    Dispatch,
} from 'redux';

interface Props {
    actions: NetteActions;
    name: string;
    href: string;
    submitId: number;
    accessKey: string;
}

interface State {
    onDeleteFile?: (accessKey: string, submitId: number) => void;
}

class File extends React.Component<Props & State, {}> {

    public render() {
        return <div className="uploaded-file">
            <button aria-hidden="true" className="pull-right btn btn-warinig" onClick={() => {
                if (window.confirm(lang.getText('Remove submit'))) {
                    this.props.onDeleteFile(this.props.accessKey, this.props.submitId);
                }
            }}>&times;</button>
            <div className="text-center p-2">
                <a href={this.props.href}>
                    <span className="display-1 w-100"><i className="fa fa-file-pdf-o"/></span>
                    <span className="d-block">{this.props.name}</span>
                </a>
            </div>

        </div>;
    }
}

const mapStateToProps = (): State => {
    return {};
};
const mapDispatchToProps = (dispatch: Dispatch<Action<string>>, ownProps: Props): State => {
    return {
        onDeleteFile: (accessKey, submitId) => deleteUploadedFile(dispatch, accessKey, submitId, ownProps.actions.revoke),
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(File);

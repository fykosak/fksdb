import { Submit } from '@apps/ajaxSubmit/middleware/';
import { Store } from '@apps/ajaxSubmit/reducers';
import { NetteActions } from '@appsCollector/netteActions';
import { dispatchFetch } from '@fetchApi/netteFetch';
import { lang } from '@i18n/i18n';
import * as React from 'react';
import { connect } from 'react-redux';
import {
    Action,
    Dispatch,
} from 'redux';

interface OwnProps {
    submit: Submit;
    accessKey: string;
}

interface DispatchProps {
    onDeleteFile(url: string): void;
}

interface StateProps {
    actions: NetteActions;
}

class File extends React.Component<OwnProps & DispatchProps & StateProps, {}> {

    public render() {
        return <div className="uploaded-file">
            <button aria-hidden="true" className="pull-right btn btn-warning" title={lang.getText('Revoke')}
                    onClick={() => {
                        if (window.confirm(lang.getText('Remove submit?'))) {
                            this.props.onDeleteFile(this.props.actions.getAction('revoke'));
                        }
                    }}>&times;</button>
            <div className="text-center p-2">
                <a href={this.props.actions.getAction('download')}>
                    <span className="display-1 w-100"><i className="fa fa-file-pdf-o"/></span>
                    <span className="d-block">{this.props.submit.name}</span>
                </a>
            </div>
        </div>;
    }
}

const mapDispatchToProps = (dispatch: Dispatch<Action<string>>, ownProps: OwnProps): DispatchProps => {
    const {accessKey} = ownProps;
    return {
        onDeleteFile: (url: string) => dispatchFetch<Submit>(url, accessKey, dispatch, JSON.stringify({})),
    };
};
const mapStateToProps = (state: Store, ownProps: OwnProps): StateProps => {
    const {accessKey} = ownProps;
    return {
        actions: state.fetchApi[accessKey].actions,
    };
};
export default connect(mapStateToProps, mapDispatchToProps)(File);

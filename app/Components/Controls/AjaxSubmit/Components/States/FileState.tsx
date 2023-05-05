import { Store } from 'FKSDB/Components/Controls/AjaxSubmit/Reducers';
import { dispatchNetteFetch } from 'vendor/fykosak/nette-frontend-component/src/fetch/redux/netteFetch';
import { NetteActions } from 'vendor/fykosak/nette-frontend-component/src/NetteActions/netteActions';
import { SubmitModel } from 'FKSDB/Models/ORM/Models/SubmitModel';
import * as React from 'react';
import { connect } from 'react-redux';
import { Action, Dispatch } from 'redux';
import { TranslatorContext } from '@translator/LangContext';

interface OwnProps {
    submit: SubmitModel;
}

interface DispatchProps {
    onDeleteFile(url: string): void;
}

interface StateProps {
    actions: NetteActions;
}

class FileState extends React.Component<OwnProps & DispatchProps & StateProps> {
    static contextType = TranslatorContext;
    public render() {
        const translator = this.context;
        const {submit} = this.props;
        return <div className="uploaded-file">
            <button aria-hidden="true" className="pull-right btn btn-outline-warning"
                    title={translator.getText('Revoke')}
                    onClick={() => {
                        if (window.confirm(translator.getText('Remove submit?'))) {
                            this.props.onDeleteFile(this.props.actions.getAction('revoke'));
                        }
                    }}>&times;</button>
            <div className="text-center p-2">
                <a href={this.props.actions.getAction('download')}>
                    <span className="display-1 w-100"><i className="fa fa-file-pdf"/></span>
                    <span className="d-block">{translator.get(submit.name)}</span>
                </a>
            </div>
        </div>;
    }
}

const mapDispatchToProps = (dispatch: Dispatch<Action<string>>): DispatchProps => {
    return {
        onDeleteFile: (url: string) => dispatchNetteFetch<SubmitModel>(url, dispatch, JSON.stringify({})),
    };
};
const mapStateToProps = (state: Store): StateProps => {
    return {
        actions: state.fetch.actions,
    };
};
export default connect(mapStateToProps, mapDispatchToProps)(FileState);

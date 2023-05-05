import { NetteActions } from 'vendor/fykosak/nette-frontend-component/src/NetteActions/netteActions';
import { Store } from 'FKSDB/Components/Controls/AjaxSubmit/Reducers';
import { SubmitModel } from 'FKSDB/Models/ORM/Models/SubmitModel';
import Card from 'FKSDB/Models/UI/Card';
import * as React from 'react';
import { connect } from 'react-redux';
import MessageBox from './MessageBox';
import File from './States/FileState';
import Form from './States/FormState';
import LoadingState from './States/LoadingState';
import { TranslatorContext } from '@translator/LangContext';
import { availableLanguage, Translator } from '@translator/translator';

interface StateProps {
    submitting: boolean;
    submit: SubmitModel;
    actions: NetteActions;
}

class UploadContainer extends React.Component<StateProps> {

    static contextType = TranslatorContext;

    public render() {
        const translator = this.context;
        const {submit} = this.props;
        if (submit === undefined) return null;
        const headline = (<>
            <h4>{translator.get(submit.name)}</h4>
            <small className="text-muted">{submit.deadline}</small>
        </>);
        return <Card headline={headline} level="info">
            <MessageBox/>
            {this.getInnerContainer(translator)}
        </Card>;
    }

    private getInnerContainer(translator: Translator<availableLanguage>) {
        const {submit, submitting} = this.props;
        if (submit.disabled) {
            return <p className="alert alert-info">{translator.getText('Task is not for your category.')}</p>;
        }
        if (submitting) {
            return (<LoadingState/>);
        }
        if (submit.isQuiz) {
            return <a className="btn btn-primary"
                      href={this.props.actions.getAction('quiz')}>{translator.getText('Submit using quiz form')}</a>;
        }
        if (submit.submitId) {
            return (<File submit={submit}/>);
        } else {
            return (<Form/>);
        }
    }
}

const mapStateToProps = (state: Store): StateProps => {
    return {
        submit: state.uploadData.submit,
        submitting: state.fetch.submitting,
        actions: state.fetch.actions,
    };
};

export default connect(mapStateToProps, null)(UploadContainer);

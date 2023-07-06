import { NetteActions } from 'vendor/fykosak/nette-frontend-component/src/NetteActions/netteActions';
import { Store } from 'FKSDB/Components/Controls/AjaxSubmit/Reducers';
import { SubmitModel } from 'FKSDB/Models/ORM/Models/submit-model';
import Card from 'FKSDB/Models/UI/card';
import * as React from 'react';
import { useContext } from 'react';
import { connect } from 'react-redux';
import MessageBox from './message-box';
import File from './States/file-state';
import Form from './States/form-state';
import LoadingState from './States/loading-state';
import { TranslatorContext } from '@translator/context';
import { availableLanguage, Translator } from '@translator/translator';

interface StateProps {
    submitting: boolean;
    submit: SubmitModel;
    actions: NetteActions;
}

function UploadContainer(props: StateProps) {

    const translator = useContext(TranslatorContext);
    const {submit, submitting, actions} = props;
    const getInnerContainer = (translator: Translator<availableLanguage>) => {
        if (submit.disabled) {
            return <p className="alert alert-info">{translator.getText('Task is not available for your category.')}</p>;
        }
        if (submitting) {
            return <LoadingState/>;
        }
        if (submit.isQuiz) {
            return <a className="btn btn-primary"
                      href={actions.getAction('quiz')}>{translator.getText('Submit using quiz form')}</a>;
        }
        if (submit.submitId) {
            return <File submit={submit}/>;
        } else {
            return <Form/>;
        }
    }
    if (submit === undefined) return null;
    const headline = (<>
        <h4>{translator.get(submit.name)}</h4>
        <small className="text-muted">{submit.deadline}</small>
    </>);
    return <Card headline={headline} level="info">
        <MessageBox/>
        {getInnerContainer(translator)}
    </Card>;


}

const mapStateToProps = (state: Store): StateProps => {
    return {
        submit: state.uploadData.submit,
        submitting: state.fetch.submitting,
        actions: state.fetch.actions,
    };
};

export default connect(mapStateToProps, null)(UploadContainer);

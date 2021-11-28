import { translator } from '@translator/translator';
import { Store } from 'FKSDB/Components/Controls/AjaxSubmit/Reducers';
import { ModelSubmit } from 'FKSDB/Models/ORM/Models/modelSubmit';
import Card from 'FKSDB/Models/UI/Card';
import * as React from 'react';
import { connect } from 'react-redux';
import MessageBox from './MessageBox';
import File from './States/FileState';
import Form from './States/FormState';
import LoadingState from './States/LoadingState';

interface StateProps {
    submitting: boolean;
    submit: ModelSubmit;
}

class UploadContainer extends React.Component<StateProps> {

    public render() {

        const {submit} = this.props;
        const headline = (<>
            <h4>{submit.name}</h4>
            <small className="text-muted">{submit.deadline}</small>
        </>);
        return <Card headline={headline} level={'info'}>
            <MessageBox/>
            {this.getInnerContainer()}
        </Card>;
    }

    private getInnerContainer() {
        const {submit, submitting} = this.props;
        if (submit.disabled) {
            return <p className="alert alert-info">{translator.getText('Task is not for your category.')}</p>;
        }
        if (submitting) {
            return (<LoadingState/>);
        }
        if (submit.isQuiz) {
            return <p className="alert alert-info">{translator.getText('Pro vyplnění kvízu použijte starý systém nahrávání.')}</p>;
        }
        if (submit.submitId) {
            return (<File submit={submit}/>);
        } else {
            return (<Form submit={submit}/>);
        }
    }
}

const mapStateToProps = (state: Store): StateProps => {
    return {
        submit: {
            ...state.uploadData.submit,
        },
        submitting: state.fetchApi.submitting,
    };
};

export default connect(mapStateToProps, null)(UploadContainer);

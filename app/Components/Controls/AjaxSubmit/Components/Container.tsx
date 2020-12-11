import * as React from 'react';
import { connect } from 'react-redux';
import { lang } from '../../../../../typescript/i18n/i18n';
import Card from '../../../../../typescript/shared/components/card';
import { Submit } from '../Middleware';
import { Store } from '../Reducers';
import MessageBox from './MessageBox';
import File from './Stetes/FileState';
import Form from './Stetes/FormState';
import LoadingState from './Stetes/LoadingState';

interface StateProps {
    submitting: boolean;
    submit: Submit;
}

class UploadContainer extends React.Component<StateProps, {}> {

    public render() {

        const {submit} = this.props;
        const headline = (<>
            <h4>{submit.name}</h4>
            <small className="text-muted">{submit.deadline}</small>
        </>);
        const {} = this.props;
        return <Card headline={headline} level={'info'}>
            <MessageBox/>
            {this.getInnerContainer()}
        </Card>;
    }

    private getInnerContainer() {
        const {submit, submitting} = this.props;
        if (submit.disabled) {
            return <p className="alert alert-info">{lang.getText('Task is not for your category.')}</p>;
        }
        if (submitting) {
            return (<LoadingState/>);
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

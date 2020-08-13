import Loading from '@apps/ajaxSubmit/components/states/loading';
import { Submit } from '@apps/ajaxSubmit/middleware/';
import { lang } from '@i18n/i18n';
import Card from '@shared/components/card';
import * as React from 'react';
import { connect } from 'react-redux';
import { Store } from '../reducers';
import MessageBox from './messageBox';
import File from './states/file';
import Form from './states/form';

interface OwnProps {
    accessKey: string;
}

interface StateProps {
    submitting: boolean;
    submit: Submit;
}

class UploadContainer extends React.Component<OwnProps & StateProps, {}> {

    public render() {

        const {submit, accessKey} = this.props;
        const headline = (<>
            <h4>{submit.name}</h4>
            <small className="text-muted">{submit.deadline}</small>
        </>);
        const {} = this.props;
        return <Card headline={headline} level={'info'}>
            <MessageBox accessKey={accessKey}/>
            {this.getInnerContainer()}
        </Card>;
    }

    private getInnerContainer() {
        const {submit, submitting, accessKey} = this.props;
        if (submit.disabled) {
            return <p className="alert alert-info">{lang.getText('Task is not for your category.')}</p>;
        }
        if (submitting) {
            return (<Loading/>);
        }
        if (submit.submitId) {
            return (<File accessKey={accessKey} submit={submit}/>);
        } else {
            return (<Form accessKey={accessKey} submit={submit}/>);
        }
    }
}

const mapStateToProps = (state: Store, ownProps: OwnProps): StateProps => {
    const {accessKey} = ownProps;
    return {
        submit: {
            ...state.uploadData.submit,
        },
        submitting: state.fetchApi.hasOwnProperty(accessKey) ? state.fetchApi[accessKey].submitting : true,
    };
};

export default connect(mapStateToProps, null)(UploadContainer);

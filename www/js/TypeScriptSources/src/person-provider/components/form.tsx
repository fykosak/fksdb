import * as React from 'react';
import { connect } from 'react-redux';
import { Dispatch } from 'redux';
import {
    submitFail,
    submitStart,
    submitSuccess,
} from '../../fetch-api/actions/submit';
import {
    netteFetch,
} from '../../fetch-api/middleware/fetch';
import { IResponse } from '../../fetch-api/middleware/interfaces';
import Lang from '../../lang/components/lang';
import {
    IRequestData,
    IResponseData,
    IStore,
} from '../interfaces';
import {
    required,
} from '../validation';

import {
    Field,
    FormSection,
} from 'redux-form';
import Login from './login';
import Password from './password';

interface IState {
    login?: any;
    password?: any;
    submitting?: boolean;
    onSubmitFail?: (e) => void;
    onSubmitStart?: () => void;
    onSubmitSuccess?: (data: IResponse<IResponseData>) => void;
}

interface IProps {
    accessKey: string;
}

class Form extends React.Component<IProps & IState, {}> {

    public render() {
        const {onSubmitSuccess, onSubmitFail, onSubmitStart, submitting} = this.props;
        const onLoginButtonClick = (event) => {

            event.preventDefault();
            onSubmitStart();
            netteFetch<IRequestData, IResponseData>({
                act: 'person-provider',
                data: {
                    fields: [],
                    login: this.props.login,
                    password: this.props.password,
                },
            }, (response) => {
                response.data.key = this.props.accessKey;
                onSubmitSuccess(response);
            }, onSubmitFail);
        };
        return <FormSection name={'personProvider'}>
            <div className={'form-group was-validated'}>
                <Field
                    name={'login'}
                    component={Login}
                    accessKey={this.props.accessKey}
                    validate={[required]}
                />
                <Field
                    name={'password'}
                    component={Password}
                    accessKey={this.props.accessKey}
                    validate={[required]}
                />

            </div>
            <button className="btn btn-primary" disabled={submitting} onClick={onLoginButtonClick}>
                <Lang text={'hledat'}/>
            </button>

        </FormSection>;
    }

}

const mapDispatchToProps = (dispatch: Dispatch<IStore>, ownProps: IProps): IState => {
    const key = 'personProvider/' + ownProps.accessKey;
    return {
        onSubmitFail: (e) => dispatch(submitFail(e, key)),
        onSubmitStart: () => dispatch(submitStart(key)),
        onSubmitSuccess: (data) =>
            dispatch(submitSuccess<IResponseData>(data, key)),
    };
};

const mapStateToProps = (state: IStore, ownProps: IProps): IState => {
    const key = 'personProvider/' + ownProps.accessKey;
    if (state.submit.hasOwnProperty(key)) {
        return {
            submitting: state.submit[key].submitting,
        };
    }
    return {};
};

export default connect(mapStateToProps, mapDispatchToProps)(Form);

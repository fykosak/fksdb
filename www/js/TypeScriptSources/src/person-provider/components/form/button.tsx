import * as React from 'react';
import { connect } from 'react-redux';
import { Dispatch } from 'redux';
import { WrappedFieldProps } from 'redux-form';
import {
    submitFail,
    submitStart,
    submitSuccess,
} from '../../../fetch-api/actions/submit';
import { netteFetch } from '../../../fetch-api/middleware/fetch';
import { IResponse } from '../../../fetch-api/middleware/interfaces';
import Lang from '../../../lang/components/lang';
import {
    IRequestData,
    IResponseData,
    IStore,
} from '../../interfaces';
import {
    isMail,
    required,
} from '../../validation';

interface IProps {
    accessKey: string;
    value: string;
}

interface IState {
    submitting?: boolean;
    onSubmitFail?: (e) => void;
    onSubmitStart?: () => void;
    onSubmitSuccess?: (data: IResponse<IResponseData>) => void;
}

class Field extends React.Component<IProps & IState & WrappedFieldProps, {}> {
    public render() {
        const {onSubmitSuccess, onSubmitFail, onSubmitStart, submitting} = this.props;
        const onFindButtonClick = (event) => {
            event.preventDefault();
            onSubmitStart();
            netteFetch<IRequestData, IResponseData>({
                act: 'person-provider',
                data: {
                    email: this.props.value,
                    fields: [],
                },
            }, (response) => {
                response.data.key = this.props.accessKey;
                onSubmitSuccess(response);
            }, onSubmitFail);
        };

        const valid = ![required, isMail].reduce((init, test) => {
                return init || test(this.props.value);
        }, '');
        return <button className="btn btn-primary" disabled={submitting || !valid} onClick={onFindButtonClick}>
            <Lang text={'search'}/>
        </button>;
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

export default connect(mapStateToProps, mapDispatchToProps)(Field);

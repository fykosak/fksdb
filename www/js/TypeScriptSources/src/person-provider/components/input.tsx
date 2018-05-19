import * as React from 'react';
import { WrappedFieldProps } from 'redux-form';
import { netteFetch } from '../../shared/helpers/fetch';
import { IReceiveData } from '../../shared/interfaces';
import {
    IReceiveProviderData,
    IResponseValues,
} from '../interfaces';

interface IProps {
    onSubmitFail: (e) => void;
    onSubmitStart: () => void;
    onSubmitSuccess: (data: IReceiveData<IReceiveProviderData>) => void;
}

export default class Input extends React.Component<IProps & WrappedFieldProps, {}> {

    public render() {
        const {meta: {error, touched, valid, warning}, input, onSubmitSuccess, onSubmitFail, onSubmitStart} = this.props;
        const onSearchButtonClick = (event) => {
            event.preventDefault();
            onSubmitStart();
            netteFetch<IResponseValues, IReceiveData<IReceiveProviderData>>({
                    act: 'person-provider',
                    email: input.value,
                    fields: [],
                }, onSubmitSuccess
                , onSubmitFail);
        };
        return <>
            <div className={'form-group ' + (touched ? 'was-validated' : 'needs-validation')}>
                <label>E-mail</label>
                <input {...input} type="email" required={true} className="form-control" placeholder="yourmail@example.com"/>
                {touched &&
                ((error && <span className="invalid-feedback">{error}</span>) ||
                    (warning && <span className="invalid-feedback">{warning}</span>))}
            </div>
            <button className="btn btn-primary" disabled={!valid} onClick={onSearchButtonClick}>Search</button>
        </>;
    }
}

import * as React from 'react';
import { netteFetch } from '../../../shared/helpers/fetch';

export default class Input extends React.Component<any, {}> {

    public render() {
        const {meta: {error, touched, valid, warning}, input} = this.props;
        const onSearchButtonClick = (event) => {
            event.preventDefault();
            this.props.onSubmitStart();
            netteFetch({
                    act: 'person-provider',
                    email: input.value,
                    fields: [],
                }, this.props.onSubmitSuccess
                , this.props.onSubmitFail);
        };
        return <>
            <div className={'form-group ' + (touched ? 'was-validated' : 'needs-validation')}>
                <label>E-mail</label>
                <input {...input} type="email" required="required" className="form-control" placeholder="yourmail@example.com"/>
                {touched &&
                ((error && <span className="invalid-feedback">{error}</span>) ||
                    (warning && <span className="invalid-feedback">{warning}</span>))}
            </div>
            <button className="btn btn-primary" disabled={!valid} onClick={onSearchButtonClick}>Search</button>
        </>;
    }
}

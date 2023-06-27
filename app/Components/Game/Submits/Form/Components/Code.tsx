import * as React from 'react';
import { WrappedFieldProps } from 'redux-form';

export default class Code extends React.Component<WrappedFieldProps, never> {

    public render() {
        const {meta: {valid}, input} = this.props;
        return (
            <span className={'form-group ' + (valid ? 'has-success' : 'has-error')}>
                <input
                    {...input}
                    maxLength={9}
                    className={'form-control-lg form-control ' + (valid ? 'is-valid' : 'is-invalid')}
                    placeholder="XXXXXXYYX"
                />
            </span>
        );
    }
}

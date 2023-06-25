import * as React from 'react';
import { WrappedFieldProps } from 'redux-form';

export default class Code extends React.Component<WrappedFieldProps, never> {
    private input: HTMLInputElement;

    public componentDidUpdate() {
        if (this.props.meta.active && this.input) {
            this.input.focus();
        }
    }
    public render() {
        const {meta: {valid}, input} = this.props;
        return (
            <span className={'form-group ' + (valid ? 'has-success' : 'has-error')}>
                <input
                    ref={(inputEl) => this.input = inputEl}
                    {...input}
                    maxLength={9}
                    className={'form-control-lg form-control ' + (valid ? 'is-valid' : 'is-invalid')}
                    placeholder="XXXXXXYYX"
                    autoFocus
                />
            </span>
        );
    }
}

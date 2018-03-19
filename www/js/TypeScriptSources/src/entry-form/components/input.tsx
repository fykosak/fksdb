import * as React from 'react';

export default class CodeInput extends React.Component<any, any> {

    public render() {
        const { meta: { valid }, input } = this.props;
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

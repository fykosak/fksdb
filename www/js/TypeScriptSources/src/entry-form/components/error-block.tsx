import * as React from 'react';
import { WrappedFieldProps } from 'redux-form';

export default class CodeInputErrorsBlock extends React.Component<WrappedFieldProps & {}, {}> {

    public render() {
        const {meta: {valid, error}} = this.props;
        return (
            <span className={'input-group ' + (valid ? 'text-success' : 'invalid-feedback')}>
                {error ? error.msg : 'OK'}
            </span>
        );
    }
}

import * as React from 'react';
import { WrappedFieldProps } from 'redux-form';

export default class ErrorBlock extends React.Component<WrappedFieldProps & Record<string, never>> {

    public render() {
        const {meta: {valid, error}} = this.props;
        return (
            <span className={'input-group ' + (valid ? 'text-success' : 'invalid-feedback')}>
                {error ? error : 'OK'}
            </span>
        );
    }
}

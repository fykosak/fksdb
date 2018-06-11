import * as React from 'react';
import { WrappedFieldProps } from 'redux-form';

export default class ErrorDisplay extends React.Component<WrappedFieldProps, {}> {
    public render() {
        const {
            meta: {touched, error},
        } = this.props;
        return <span className="invalid-feedback">{touched && (error)}</span>;
    }
}

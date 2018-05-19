import * as React from 'react';
import { WrappedFieldProps } from 'redux-form';

interface IProps {

}

export default class ErrorDisplay extends React.Component<WrappedFieldProps & IProps, {}> {
    public render() {
        const {
            meta: {touched, error, warning},
        } = this.props;
        return <div>{touched && (
            (error && <span>{error}</span>) ||
            (warning && <span>{warning}</span>))
        }</div>;

    }
}

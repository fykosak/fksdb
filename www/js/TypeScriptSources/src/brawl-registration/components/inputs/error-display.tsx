import * as React from 'react';
import { WrappedFieldProps } from 'redux-form';

interface IProps {

}

export default class ErrorDisplay extends React.Component<WrappedFieldProps & IProps, {}> {
    public render() {
        const {
            meta: {touched, error, warning},
        } = this.props;
        // div className={(invalid ? ' is-invalid' : '')}
        return <span className="invalid-feedback">{touched && (error)}</span>;

    }
}

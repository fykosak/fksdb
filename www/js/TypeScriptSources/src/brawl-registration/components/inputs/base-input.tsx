import * as React from 'react';
import { WrappedFieldProps } from 'redux-form';
import { IInputProps } from '../../../person-provider/components/input-provider';
import ErrorDisplay from './error-display';

export default class BaseInput extends React.Component<WrappedFieldProps & IInputProps, {}> {

    public render() {
        const {
            input,
            type,
            readOnly,
            meta,
            meta: {invalid, touched},
            JSXDescription,
            JSXLabel,
        } = this.props;

        return <div className="form-group">
            <label>{JSXLabel}</label>
            {JSXDescription && (<small className="form-text text-muted">{JSXDescription}</small>)}
            <input
                className={'form-control' + (touched && invalid ? ' is-invalid' : '')}
                readOnly={readOnly}
                {...input}
                type={type}
            />
            <ErrorDisplay input={input} meta={meta}/>
        </div>;
    }
}

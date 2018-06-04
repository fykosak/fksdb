import * as React from 'react';
import { WrappedFieldProps } from 'redux-form';
import ErrorDisplay from './error-display';
import { IInputProps } from './input';

export default class BaseInput extends React.Component<WrappedFieldProps & IInputProps, {}> {

    public componentDidMount() {
        if (this.props.providerOptions && this.props.providerOptions.hasValue) {
            this.props.input.onChange(this.props.providerOptions.value);
        }
    }

    public render() {
        const {
            input,
            type,
            readOnly,
            meta,
            meta: {invalid, touched},
            description,
            JSXLabel,
        } = this.props;

        return <div className="form-group">
            <label>{JSXLabel}</label>
            {description && (<small className="form-text text-muted">{description}</small>)}
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

import * as React from 'react';
import { WrappedFieldProps } from 'redux-form';
import ErrorDisplay from './error-display';

export interface IBaseInputProps {
    inputType: string;
    readonly: boolean;
    placeholder?: string;
    JSXDescription?: JSX.Element;
    JSXLabel: JSX.Element;
}

export default class BaseInput extends React.Component<WrappedFieldProps & IBaseInputProps, {}> {

    public render() {
        const {
            input,
            inputType,
            readonly,
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
                readOnly={readonly}
                {...input}
                type={inputType}
            />
            <ErrorDisplay input={input} meta={meta}/>
        </div>;
    }
}

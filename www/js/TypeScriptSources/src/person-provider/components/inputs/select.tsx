import * as React from 'react';
import { WrappedFieldProps } from 'redux-form';
import ErrorDisplay from './error-display';

export interface ISelectInputProps {
    JSXLabel: JSX.Element;
    JSXDescription?: JSX.Element;
    children: any;
    readonly: boolean;
}

export default class Select extends React.Component<WrappedFieldProps & ISelectInputProps, {}> {

    public render() {
        const {
            input,
            JSXLabel,
            JSXDescription,
            meta,
            readonly,
        } = this.props;

        return <div className="form-group">
            <label>{JSXLabel}</label>
            {JSXDescription && (<small className="form-text text-muted">{JSXDescription}</small>)}
            <select className="form-control" {...input} disabled={readonly}>
                {this.props.children}
            </select>
            <ErrorDisplay input={input} meta={meta}/>
        </div>;
    }
}

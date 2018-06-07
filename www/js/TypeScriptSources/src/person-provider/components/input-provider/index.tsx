import * as React from 'react';
import { WrappedFieldProps } from 'redux-form';
import Input from './input';
import SecureDisplay from './secure-display';

interface IProps {
    secure: boolean;
    providerInput: React.ComponentClass<IInputProps & WrappedFieldProps>;
    modifiable: boolean;
    required: boolean;
    accessKey: string;
}

export interface IInputProps {
    type?: string;
    readOnly?: boolean;
    placeholder?: string;
    JSXLabel: JSX.Element;
    JSXDescription?: JSX.Element;
}

export default class InputProvider extends React.Component<IInputProps & IProps & WrappedFieldProps, {}> {

    public render() {
        const {
            accessKey,
            JSXLabel,
            secure,
            modifiable,
            type,
            placeholder,
            providerInput,
            JSXDescription,
            meta,
            input,
        } = this.props;
        const props = {
            JSXDescription,
            JSXLabel,
            input,
            meta,
            placeholder,
            readOnly: !modifiable,
            type,
        };
        const child = React.createElement<IInputProps>(providerInput, props);
        const inputProvider = <Input input={input} meta={meta} accessKey={accessKey}/>;
        if (secure) {
            return <>
                {inputProvider}
                <SecureDisplay accessKey={accessKey} input={input} meta={meta} JSXLabel={JSXLabel}>
                    {child}
                </SecureDisplay>
            </>;
        }

        return <>
            {inputProvider}
            {child}
        </>;
    }
}

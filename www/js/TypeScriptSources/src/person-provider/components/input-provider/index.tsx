import * as React from 'react';
import { WrappedFieldProps } from 'redux-form';
import Input from './input';
import SecureDisplay from './secure-display';

interface IProps<P = {}> extends IBaseInputProps {
    secure: boolean;
    providerInput: React.ComponentClass<WrappedFieldProps & P & IBaseInputProps>;
    accessKey: string;
}

interface IBaseInputProps {
    JSXLabel: JSX.Element;
}

export default class InputProvider<P= IBaseInputProps> extends React.Component<IProps<P> & P & WrappedFieldProps, {}> {

    public render() {
        const {
            accessKey,
            secure,
            JSXLabel,
            providerInput,
            meta,
            input,
        } = this.props;

        const child = React.createElement<any>(providerInput, this.props);
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

import * as React from 'react';
import { WrappedFieldProps } from 'redux-form';
import Input from './input';
import SecureDisplay from './secure-display';
import { IInputDefinition } from '../fields/interfaces';

interface IProps<P = {}> extends IBaseInputProps {
    inputDef: IInputDefinition;
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
            inputDef,
        } = this.props;

        const child = React.createElement<any>(providerInput, this.props);

        if (secure) {
            return <>
                <Input input={input} meta={meta} inputDef={inputDef}/>
                <SecureDisplay accessKey={accessKey} input={input} meta={meta} JSXLabel={JSXLabel}/>
            </>;
        }
        return <>
            <Input input={input} meta={meta} inputDef={inputDef}/>
            {child}
        </>;
    }
}

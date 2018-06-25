import * as React from 'react';
import { WrappedFieldProps } from 'redux-form';
import { IPersonSelector } from '../../../brawl-registration/middleware/price';
import { IInputDefinition } from '../fields/interfaces';
import Input from './input';
import SecureDisplay from './secure-display';

interface IProps<P = {}> extends IBaseInputProps {
    inputDef: IInputDefinition;
    providerInput: React.ComponentClass<WrappedFieldProps & P & IBaseInputProps>;
    personSelector: IPersonSelector;
}

interface IBaseInputProps {
    JSXLabel: JSX.Element;
}

export default class InputProvider<P= IBaseInputProps> extends React.Component<IProps<P> & P & WrappedFieldProps, {}> {

    public render() {
        const {
            personSelector,
            JSXLabel,
            providerInput,
            meta,
            input,
            inputDef,
        } = this.props;

        const child = React.createElement<any>(providerInput, this.props);

        if (inputDef.secure) {
            return <>
                <Input input={input} meta={meta} inputDef={inputDef}/>
                <SecureDisplay personSelector={personSelector} input={input} meta={meta} JSXLabel={JSXLabel}/>
            </>;
        }
        return <>
            <Input input={input} meta={meta} inputDef={inputDef}/>
            {child}
        </>;
    }
}

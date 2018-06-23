import * as React from 'react';
import { Field } from 'redux-form';
import BaseInput, { IBaseInputProps } from '../../../../brawl-registration/components/inputs/base-input';
import {
    IPersonSelector,
} from '../../../../brawl-registration/middleware/price';
import Lang from '../../../../lang/components/lang';
import { required as requiredTest } from '../../../validation';
import InputProvider from '../../input-provider';
import { IInputDefinition } from '../interfaces';

class Input extends InputProvider<IBaseInputProps> {
}

interface IProps {
    def: IInputDefinition;
    personSelector: IPersonSelector;
    name: string;
}

export default class IdNumber extends React.Component<IProps, {}> {

    public render() {
        const {personSelector: {accessKey}, def: {required, readonly, secure}, name} = this.props;
        return <Field
            accessKey={accessKey}
            name={name}
            component={Input}
            JSXLabel={<Lang text={'Číslo OP/pasu'}/>}
            inputType={'text'}
            secure={secure}
            providerInput={BaseInput}
            readOnly={readonly}
            noChangeMode={false}
            validate={required ? [requiredTest] : []}
        />;
    }
}

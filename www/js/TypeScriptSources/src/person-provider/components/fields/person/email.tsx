import * as React from 'react';
import { Field } from 'redux-form';
import BaseInput, { IBaseInputProps } from '../../../../brawl-registration/components/inputs/base-input';
import { IPersonSelector } from '../../../../brawl-registration/middleware/price';
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

export default class Email extends React.Component<IProps, {}> {
    public render() {
        const {personSelector: {accessKey}, def: {required, readonly, secure}, name, def} = this.props;
        return <Field
            name={name}
            inputDef={def}
            accessKey={accessKey}
            JSXLabel={<Lang text={'E-mail'}/>}
            inputType={'text'}
            component={Input}
            providerInput={BaseInput}
            placeholder={'Name'}
            readOnly={readonly}
            secure={secure}
            noChangeMode={true}
            validate={required ? [requiredTest] : []}
        />;
    }
}

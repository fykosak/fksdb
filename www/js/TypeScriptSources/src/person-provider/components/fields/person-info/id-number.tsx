import * as React from 'react';
import { Field } from 'redux-form';
import { IPersonSelector } from '../../../../brawl-registration/middleware/price';
import Lang from '../../../../lang/components/lang';
import { required as requiredTest } from '../../../validation';
import InputProvider from '../../input-provider';
import BaseInput, { IBaseInputProps } from '../../inputs/base-input';
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
        const {personSelector, def: {required, readonly, secure}, name, def} = this.props;
        return <Field
            name={name}
            inputDef={def}
            component={Input}
            JSXLabel={<Lang text={'Číslo OP/pasu'}/>}
            inputType={'text'}
            providerInput={BaseInput}
            readonly={readonly}
            personSelector={personSelector}
            validate={required ? [requiredTest] : []}
        />;
    }
}

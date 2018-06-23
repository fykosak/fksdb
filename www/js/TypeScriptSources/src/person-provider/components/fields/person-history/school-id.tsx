import * as React from 'react';
import { Field } from 'redux-form';
import { IPersonSelector } from '../../../../brawl-registration/middleware/price';
import Lang from '../../../../lang/components/lang';
import SchoolProvider, { ISchoolProviderInputProps } from '../../../../school-provider/';
import { required as requiredTest } from '../../../validation';
import InputProvider from '../../input-provider';
import { IInputDefinition } from '../interfaces';

class Input extends InputProvider<ISchoolProviderInputProps> {
}

interface IProps {
    def: IInputDefinition;
    personSelector: IPersonSelector;
    name: string;
}

export default class SchoolId extends React.Component<IProps, {}> {

    public render() {
        const {personSelector: {accessKey}, def: {required, readonly, secure}, name} = this.props;
        return <Field
            accessKey={accessKey}
            JSXLabel={<Lang text={'School'}/>}
            providerInput={SchoolProvider}
            secure={secure}
            component={Input}
            readonly={readonly}
            name={name}
            validate={required ? [requiredTest] : []}
        />;
    }
}

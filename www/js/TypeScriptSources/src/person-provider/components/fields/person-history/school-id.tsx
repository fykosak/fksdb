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
        const {personSelector, def: {required, readonly, secure}, name, def} = this.props;
        return <Field
            inputDef={def}
            JSXLabel={<Lang text={'School'}/>}
            providerInput={SchoolProvider}
            component={Input}
            readonly={readonly}
            personSelector={personSelector}
            name={name}
            validate={required ? [requiredTest] : []}
        />;
    }
}

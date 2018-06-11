import * as React from 'react';
import { Field } from 'redux-form';
import BaseInput, { IBaseInputProps } from '../../../../brawl-registration/components/inputs/base-input';
import { IPersonStringSelectror } from '../../../../brawl-registration/middleware/price';
import Lang from '../../../../lang/components/lang';
import { required } from '../../../validation';
import InputProvider from '../../input-provider';

class Input extends InputProvider<IBaseInputProps> {
}

export default class FamilyName extends React.Component<IPersonStringSelectror, {}> {
    public render() {
        const {accessKey} = this.props;
        return <Field
            accessKey={accessKey}
            name={'person.familyName'}
            JSXLabel={<Lang text={'Family name'}/>}
            inputType={'text'}
            component={Input}
            providerInput={BaseInput}
            placeholder={'Name'}
            readOnly={false}
            secure={false}
            validate={[required]}
        />;
    }
}

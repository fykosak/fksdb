import * as React from 'react';
import { Field } from 'redux-form';
import Lang from '../../../../lang/components/lang';
import InputProvider from '../../../../person-provider/components/input-provider';
import { getFieldName } from '../../../middleware/person';
import { IPersonSelector } from '../../../middleware/price';
import BaseInput from '../../inputs/base-input';

export default class FamilyName extends React.Component<IPersonSelector, {}> {
    public render() {
        return <Field
            accessKey={getFieldName(this.props.type, this.props.index)}
            name={'person.familyName'}
            JSXLabel={<Lang text={'Family name'}/>}
            type={'text'}
            component={InputProvider}
            providerInput={BaseInput}
            placeholder={'Name'}
            modifiable={true}
            secure={false}
            required={true}
        />;
    }
}

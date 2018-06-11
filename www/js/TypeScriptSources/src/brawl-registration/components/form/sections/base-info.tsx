import * as React from 'react';
import { Field } from 'redux-form';
import Lang from '../../../../lang/components/lang';
import FamilyName from '../../../../person-provider/components/fields/person/family-name';
import OtherName from '../../../../person-provider/components/fields/person/other-name';
import InputProvider from '../../../../person-provider/components/input-provider';
import { getFieldName } from '../../../middleware/person';
import { IPersonSelector } from '../../../middleware/price';
import BaseInput, { IBaseInputProps } from '../../inputs/base-input';
import { required } from '../../../../person-provider/validation';

class Input extends InputProvider<IBaseInputProps> {
}

export default class BaseInfoSection extends React.Component<IPersonSelector, {}> {
    public render() {
        const {type, index} = this.props;
        const accessKey = getFieldName(type, index);
        return <div className={'form-section'}>
            <h3><Lang text={'Base info'}/></h3>
            <FamilyName accessKey={accessKey}/>
            <OtherName accessKey={accessKey}/>
            <Field
                accessKey={getFieldName(type, index)}
                component={Input}
                name={'email'}
                JSXLabel={<Lang text={'E-mail'}/>}
                inputType={'email'}
                providerInput={BaseInput}
                placeholder={'youmail@example.com'}
                readOnly={true}
                secure={false}
                validate={[required]}
            />
        </div>;
    }
}

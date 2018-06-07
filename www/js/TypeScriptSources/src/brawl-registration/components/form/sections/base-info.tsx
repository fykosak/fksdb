import * as React from 'react';
import { Field } from 'redux-form';
import Lang from '../../../../lang/components/lang';
import InputProvider from '../../../../person-provider/components/input-provider';
import { getFieldName } from '../../../middleware/person';
import { IPersonSelector } from '../../../middleware/price';
import FamilyName from '../../fields/person/family-name';
import OtherName from '../../fields/person/other-name';
import BaseInput from '../../inputs/base-input';

export default class BaseInfoSection extends React.Component<IPersonSelector, {}> {
    public render() {
        const {type, index} = this.props;
        return <div className={'form-section'}>
            <h3><Lang text={'Base info'}/></h3>
            <FamilyName type={type} index={index}/>
            <OtherName type={type} index={index}/>
            <Field
                accessKey={getFieldName(type, index)}
                component={InputProvider}
                name={'email'}
                JSXLabel={<Lang text={'E-mail'}/>}
                type={'email'}
                providerInput={BaseInput}
                placeholder={'youmail@example.com'}
                modifiable={false}
                secure={false}
                required={true}
            />
        </div>;
    }
}

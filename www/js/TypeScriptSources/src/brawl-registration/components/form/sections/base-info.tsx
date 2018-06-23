import * as React from 'react';
import { Field } from 'redux-form';
import Lang from '../../../../lang/components/lang';
import FamilyName from '../../../../person-provider/components/fields/person/family-name';
import OtherName from '../../../../person-provider/components/fields/person/other-name';
import InputProvider from '../../../../person-provider/components/input-provider';
import { required } from '../../../../person-provider/validation';
import BaseInput, { IBaseInputProps } from '../../inputs/base-input';
import { IPersonSelector } from '../../../middleware/price';

class Input extends InputProvider<IBaseInputProps> {
}

interface IProps {
    personSelector: IPersonSelector;
}

export default class BaseInfoSection extends React.Component<IProps, {}> {
    public render() {
        const {personSelector: {accessKey}} = this.props;

        return <div className={'form-section'}>
            <h3><Lang text={'Base info'}/></h3>
            <FamilyName accessKey={accessKey}/>
            <OtherName accessKey={accessKey}/>
            <Field
                accessKey={accessKey}
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

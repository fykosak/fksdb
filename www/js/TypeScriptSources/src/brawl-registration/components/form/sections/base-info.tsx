import * as React from 'react';
import { FormSection } from 'redux-form';
import Lang from '../../../../lang/components/lang';
import BaseInput from '../../inputs/base-input';
import Input from '../../inputs/input';

interface IProps {
    type: string;
    index: number;
    providerOpt: {
        email: { hasValue: boolean; value: string };
        familyName: { hasValue: boolean; value: string };
        otherName: { hasValue: boolean; value: string };
    };
}

export default class BaseInfoGroup extends React.Component<IProps, {}> {
    public render() {
        const {providerOpt: {otherName, familyName, email}} = this.props;

        return <FormSection name={'personInfo'}>
            <h3><Lang text={'Base info'}/></h3>
            <Input name={'otherName'}
                   label={<Lang text={'Other name'}/>}
                   type={'text'}
                   component={BaseInput}
                   placeholder={'Name'}
                   providerOptions={otherName}
                   modifiable={true}
                   secure={false}
                   required={true}
            />

            <Input name={'familyName'}
                   label={<Lang text={'Family name'}/>}
                   type={'text'}
                   component={BaseInput}
                   placeholder={'Name'}
                   providerOptions={familyName}
                   modifiable={true}
                   secure={false}
                   required={true}
            />
            <Input
                name={'email'}
                label={<Lang text={'E-mail'}/>}
                type={'email'}
                component={BaseInput}
                placeholder={'youmail@example.com'}
                providerOptions={email}
                modifiable={false}
                secure={false}
                required={true}
            />
        </FormSection>;
    }
}

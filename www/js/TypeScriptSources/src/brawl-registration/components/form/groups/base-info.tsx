import * as React from 'react';
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

        return <div>
            <h3>Base info</h3>
            <Input name={'otherName'}
                   label={'Other name'}
                   type={'text'}
                   component={BaseInput}
                   placeholder={'Name'}
                   providerOptions={otherName}
                   modifiable={true}
                   secure={false}
                   required={true}
            />

            <Input name={'familyName'}
                   label={'Family name'}
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
                label={'E-mail'}
                type={'email'}
                component={BaseInput}
                placeholder={'youmail@example.com'}
                providerOptions={email}
                modifiable={false}
                secure={false}
                required={true}
            />
        </div>;
    }
}

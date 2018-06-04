import * as React from 'react';
import { connect } from 'react-redux';
import { FormSection } from 'redux-form';
import Lang from '../../../../lang/components/lang';
import { IProviderValue } from '../../../../person-provider/interfaces';
import { getFieldName } from '../../../middleware/person';
import { IPersonSelector } from '../../../middleware/price';
import { IStore } from '../../../reducers';
import BaseInput from '../../inputs/base-input';
import Input from '../../inputs/input';

interface IState {
    email?: IProviderValue<string>;
    familyName?: IProviderValue<string>;
    otherName?: IProviderValue<string>;
}

class BaseInfoSection extends React.Component<IPersonSelector & IState, {}> {
    public render() {
        const {otherName, familyName, email} = this.props;

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

const mapDispatchToProps = (): IState => {
    return {};
};

const mapStateToProps = (state: IStore, ownProps: IPersonSelector): IState => {
    const accessKey = getFieldName(ownProps.type, ownProps.index);
    if (state.provider.hasOwnProperty(accessKey)) {
        return {
            email: state.provider[accessKey].fields.email,
            familyName: state.provider[accessKey].fields.familyName,
            otherName: state.provider[accessKey].fields.otherName,
        };
    }
    return {};
};

export default connect(mapStateToProps, mapDispatchToProps)(BaseInfoSection);

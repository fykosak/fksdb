import * as React from 'react';
import { Field } from 'redux-form';
import { IPersonStringSelectror } from '../../../../brawl-registration/middleware/price';
import Lang from '../../../../lang/components/lang';
import SchoolProvider, { ISchoolProviderInputProps } from '../../../../school-provider/';
import { required } from '../../../validation';
import InputProvider from '../../input-provider';

class Input extends InputProvider<ISchoolProviderInputProps> {
}

export default class SchoolId extends React.Component<IPersonStringSelectror, {}> {

    public render() {
        const {accessKey} = this.props;
        return <Field
            accessKey={accessKey}
            JSXLabel={<Lang text={'School'}/>}
            providerInput={SchoolProvider}
            secure={true}
            component={Input}
            name={'personHistory.schoolId'}
            validate={[required]}
        />;
    }
}

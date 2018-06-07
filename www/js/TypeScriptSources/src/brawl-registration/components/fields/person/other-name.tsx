import * as React from 'react';
import Lang from '../../../../lang/components/lang';
import InputProvider from '../../../../person-provider/components/input-provider';

import { IPersonSelector } from '../../../middleware/price';
import BaseInput from '../../inputs/base-input';
import { Field } from 'redux-form';
import { getFieldName } from '../../../middleware/person';

export default class OtherName extends React.Component<IPersonSelector, {}> {
    public render() {
        return <Field
            name={'person.otherName'}
            accessKey={getFieldName(this.props.type, this.props.index)}
            JSXLabel={<Lang text={'Other name'}/>}
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

import * as React from 'react';
import { FormSection } from 'redux-form';
import Lang from '../../../../lang/components/lang';
import { IPersonSelector } from '../../../middleware/price';
import Accommodation from '../fields/accommodation';

export default class AccommodationSection extends React.Component<IPersonSelector, {}> {
    public render() {
        const {index, type} = this.props;

        return <FormSection name={'accommodation'}>
            <h3><Lang text={'Accommodation'}/></h3>
            <Accommodation type={type} index={index}/>
        </FormSection>;
    }
}

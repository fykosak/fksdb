import * as React from 'react';
import { FormSection } from 'redux-form';
import Lang from '../../../../lang/components/lang';
import Accommodation from '../fields/accommodation';

interface IProps {
    type: string;
    index: number;
}

export default class AccommodationGroup extends React.Component<IProps, {}> {
    public render() {
        const {index, type} = this.props;

        return <FormSection name={'accommodation'}>
            <h3><Lang text={'Accommodation'}/></h3>
            <Accommodation type={type} index={index}/>
        </FormSection>;
    }
}

import * as React from 'react';
import { FormSection } from 'redux-form';
import Lang from '../../../../lang/components/lang';
import BaseInput from '../../inputs/base-input';
import Input from '../../inputs/input';
import Accommodation from '../sections/accommodation';

interface IProps {
    type: string;
    index: number;
    accommodation: any;
    providerOpt: {
        idNumber: { hasValue: boolean; value: string };
    };
}

export default class AccommodationGroup extends React.Component<IProps, {}> {
    public render() {
        const {providerOpt: {idNumber}, accommodation} = this.props;
        let hasAccommodation = false;
        for (const date in accommodation) {
            if (accommodation.hasOwnProperty(date)) {
                hasAccommodation = hasAccommodation || accommodation[date];
            }
        }

        return <div>
            <h3><Lang text={'Accommodation'}/></h3>
            <FormSection name={'accommodation'}>
                <Accommodation type={this.props.type} index={this.props.index}/>
            </FormSection>
            {hasAccommodation && (
                <Input label={'Číslo OP/pasu'}
                       type={'text'}
                       secure={true}
                       description={'Kvôli ubytovaniu.'}
                       component={BaseInput}
                       modifiable={true}
                       name={'idNumber'}
                       providerOptions={idNumber}
                       required={true}
                />)
            }
        </div>;
    }
}

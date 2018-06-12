import * as React from 'react';
import { connect } from 'react-redux';
import { FormSection } from 'redux-form';
import { FORM_NAME } from '../';
import Lang from '../../../../lang/components/lang';
import {
    IAccommodationItem,
    IScheduleItem,
} from '../../../middleware/iterfaces';
import {
    getAccommodationFromState,
    getAccommodationPrice,
    getScheduleFromState,
    getSchedulePrice,
} from '../../../middleware/price';
import PriceDisplay from '../../displays/price';

interface IProps {
    type: string;
    index: number;
}

interface IState {
    schedule?: any;
    scheduleDef?: IScheduleItem[];
    accommodation?: any;
    accommodationDef?: IAccommodationItem[];
}

class Price extends React.Component<IProps & IState, {}> {

    public render() {
        const {scheduleDef, schedule, accommodation, accommodationDef} = this.props;

        const schedulePrice = getSchedulePrice(scheduleDef, schedule);
        const accommodationPrice = getAccommodationPrice(accommodationDef, accommodation);

        return <FormSection name={'price'}>
            <h3><Lang text={'Celková cena pre osobu'}/></h3>
            <PriceDisplay eur={accommodationPrice.eur + schedulePrice.eur} kc={accommodationPrice.kc + schedulePrice.kc}/>
        </FormSection>;
    }
}

const mapDispatchToProps = (): IState => {
    return {};
};

const mapStateToProps = (state, ownProps: IProps): IState => {
    return {
        accommodation: getAccommodationFromState(FORM_NAME, state, ownProps),
        accommodationDef: state.definitions.accommodation,
        schedule: getScheduleFromState(FORM_NAME, state, ownProps),
        scheduleDef: state.definitions.schedule,
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(Price);

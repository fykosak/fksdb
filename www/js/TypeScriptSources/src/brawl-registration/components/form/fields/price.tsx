import * as React from 'react';
import { connect } from 'react-redux';
import { FormSection } from 'redux-form';
import { FORM_NAME } from '../';
import Lang from '../../../../lang/components/lang';
import {
    getAccommodationFromState,
    getAccommodationPrice,
} from '../../../../person-provider/components/fields/person-accommodation/accommodation/helpers';
import { IAccommodationItem } from '../../../../person-provider/components/fields/person-accommodation/accommodation/interfaces';
import {
    IScheduleItem,
} from '../../../middleware/iterfaces';
import {
    getScheduleFromState,
    getSchedulePrice,
    IPersonSelector,
} from '../../../middleware/price';
import PriceDisplay from '../../../../shared/components/displays/price/';

interface IProps {
    personSelector: IPersonSelector;
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
            <PriceDisplay price={{
                eur: (accommodationPrice.eur + schedulePrice.eur),
                kc: (accommodationPrice.kc + schedulePrice.kc),
            }}/>
        </FormSection>;
    }
}

const mapDispatchToProps = (): IState => {
    return {};
};

const mapStateToProps = (state, ownProps: IProps): IState => {
    return {
        accommodation: getAccommodationFromState(FORM_NAME, state, ownProps.personSelector),
        accommodationDef: state.definitions.accommodation,
        schedule: getScheduleFromState(FORM_NAME, state, ownProps.personSelector),
        scheduleDef: state.definitions.schedule,
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(Price);

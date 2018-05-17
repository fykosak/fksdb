import * as React from 'react';
import { connect } from 'react-redux';
import {
    IAccommodationItem,
    IScheduleItem,
} from '../../middleware/iterfaces';
import {
    getAccommodationFromState,
    getAccommodationPrice,
    getScheduleFromState,
    getSchedulePrice,
} from '../../middleware/price';
import PriceDisplay from '../displays/price';
import { FORM_NAME } from '../form';

interface IProps {
    type: string;
    index: number;
}

interface IState {
    schedule?: any;
    scheduleDef?: IScheduleItem[];
    acc?: any;
    accommodationDef?: IAccommodationItem[];
}

class Price extends React.Component<IProps & IState, {}> {

    public render() {
        const schedulePrice = getSchedulePrice(this.props.scheduleDef, this.props.schedule);
        const accommodationPrice = getAccommodationPrice(this.props.accommodationDef, this.props.acc);

        return <div>
            <p>Celková cena pre osobu</p>
            <PriceDisplay eur={accommodationPrice.eur + schedulePrice.eur} kc={accommodationPrice.kc + schedulePrice.kc}/>
        </div>;
    }
}

const mapDispatchToProps = (): IState => {
    return {};
};

const mapStateToProps = (state, ownProps: IProps): IState => {
    return {
        scheduleDef: state.definitions.schedule,
        ...getScheduleFromState(FORM_NAME, state, ownProps),
        accommodationDef: state.definitions.accommodation,
        ...getAccommodationFromState(FORM_NAME, state, ownProps),
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(Price);

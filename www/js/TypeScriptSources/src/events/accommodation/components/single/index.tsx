import * as React from 'react';
import { connect } from 'react-redux';
import { Dispatch } from 'redux';
import { lang } from '../../../../i18n/i18n';
import PriceDisplay from '../../../../shared/components/displays/price';
import { changeAccommodation } from '../../actions';
import { recalculateDate } from '../../middleware/dates';
import { IEventAccommodation } from '../../middleware/interfaces';
import { IAccommodationStore } from '../../reducer';
import CapacityLabel from '../capacity-label';

interface IProps {
    accommodationDef?: IEventAccommodation[];
}

interface IState {
    onChange?: (date: string, value: number) => void;
    value?: number;
}

class Single extends React.Component<IProps & IState, {}> {

    public render() {
        const {accommodationDef, value, onChange} = this.props;
        if (accommodationDef.length !== 1 && accommodationDef.hasOwnProperty(0)) {
            throw new Error('Wrong type of accommodation');
        }
        const {date, eventAccommodationId, price, name, capacity, usedCapacity} = accommodationDef[0];
        const {fromDate, toDate} = recalculateDate(date);

        const label = lang.getText('I want to stay in the Hotel "%name%" from %from% to %to%.')
            .replace('%name%', name)
            .replace('%from%', fromDate.toLocaleDateString(lang.getBCP47()))
            .replace('%to%', toDate.toLocaleDateString(lang.getBCP47()));

        return <div>
                <span className={'form-check ' + (value ? 'text-success border-success' : '')}>
                <span
                    className={value ? 'fa fa-check-square-o' : 'fa fa-square-o'}
                    onClick={() => {
                        value ? onChange(date, null) : onChange(date, eventAccommodationId);
                    }}
                />
                <span className={'ml-3'}>{label}</span>
            </span>
            <span className={'text-muted'}>
                <small className={'ml-3'}>{lang.getText(' Accommodation price:')} <PriceDisplay price={price}/></small>
                <CapacityLabel capacity={capacity} usedCapacity={usedCapacity}/>
            </span>

        </div>;
    }
}

const mapDispatchToProps = (dispatch: Dispatch<IAccommodationStore>): IState => {
    return {
        onChange: (date, value) => dispatch(changeAccommodation(date, value)),
    };
};

const mapStateToProps = (state, ownProps: IProps): IState => {

    const {accommodationDef} = ownProps;
    if (accommodationDef.length !== 1) {
        throw new Error();
    }
    let value = null;
    if (state.accommodation.hasOwnProperty(accommodationDef[0].date)) {
        value = state.accommodation[accommodationDef[0].date];
    }
    return {
        value,
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(Single);

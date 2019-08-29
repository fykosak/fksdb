import { lang } from '@i18n/i18n';
import { changeData } from '@inputConnector/actions';
import PriceDisplay from '@shared/components/displays/price/';
import * as React from 'react';
import { connect } from 'react-redux';
import { Dispatch } from 'redux';
import { recalculateDate } from '../../middleware/dates';
import { EventAccommodation } from '../../middleware/interfaces';
import { Store } from '../../reducer';
import CapacityLabel from '../capacityLabel';

interface Props {
    accommodationItem?: EventAccommodation;
}

interface State {
    onChange?: (date: string, value: number) => void;
    value?: number;
}

class Single extends React.Component<Props & State, {}> {

    public render() {
        const {accommodationItem, value, onChange} = this.props;
        const {date, eventAccommodationId, price, name, capacity, usedCapacity} = accommodationItem;
        const {fromDate, toDate} = recalculateDate(date);

        const label = lang.getText('I want to stay in the hotel %name% from %from% to %to%.')
            .replace('%name%', name)
            .replace('%from%', fromDate.toLocaleDateString(lang.getBCP47()))
            .replace('%to%', toDate.toLocaleDateString(lang.getBCP47()));
        const isChecked = (value === eventAccommodationId);
        return <div className={'mb-3'}>
                <span className={'form-check ' + (isChecked ? 'text-success border-success' : '')}>
                <span
                    className={isChecked ? 'fa fa-check-square-o' : 'fa fa-square-o'}
                    onClick={() => {
                        isChecked ? onChange(date, null) : onChange(date, eventAccommodationId);
                    }}
                />
                <span className={'ml-3'}>{label}</span>
            </span>
            <span className={'text-muted'}>
                <small className={'ml-3'}>{lang.getText('Accommodation price')}: <PriceDisplay price={price}/></small>
                <CapacityLabel capacity={capacity} usedCapacity={usedCapacity}/>
            </span>
        </div>;
    }
}

const mapDispatchToProps = (dispatch: Dispatch): State => {
    return {
        onChange: (date, value) => dispatch(changeData(date, value)),
    };
};

const mapStateToProps = (state: Store, ownProps: Props): State => {
    const {accommodationItem} = ownProps;
    let value = null;
    if (state.inputConnector.data.hasOwnProperty(accommodationItem.date)) {
        value = state.inputConnector.data[accommodationItem.date];
    }
    return {
        value,
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(Single);

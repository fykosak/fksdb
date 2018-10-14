import * as React from 'react';
import { connect } from 'react-redux';
import { Dispatch } from 'redux';
import { lang } from '../../../../i18n/i18n';
import PriceDisplay from '../../../../shared/components/displays/price';
import { recalculateDate } from '../../middleware/dates';
import { IEventAccommodation } from '../../middleware/interfaces';
import { IAccommodationStore } from '../../reducer';
import CapacityLabel from '../capacity-label';
import { changeData } from '../../../../input-connector/actions';

interface IProps {
    accommodationItem?: IEventAccommodation;
}

interface IState {
    onChange?: (date: string, value: number) => void;
    value?: number;
}

class Single extends React.Component<IProps & IState, {}> {

    public render() {
        const {accommodationItem, value, onChange} = this.props;
        const {date, eventAccommodationId, price, name, capacity, usedCapacity} = accommodationItem;
        const {fromDate, toDate} = recalculateDate(date);

        const label = lang.getText('I want to stay in the hotel %name% from %from% to %to%.')
            .replace('%name%', name)
            .replace('%from%', fromDate.toLocaleDateString(lang.getBCP47()))
            .replace('%to%', toDate.toLocaleDateString(lang.getBCP47()));

        return <div className={'mb-3'}>
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
                <small className={'ml-3'}>{lang.getText('Accommodation price')}: <PriceDisplay price={price}/></small>
                <CapacityLabel capacity={capacity} usedCapacity={usedCapacity}/>
            </span>
        </div>;
    }
}

const mapDispatchToProps = (dispatch: Dispatch<IAccommodationStore>): IState => {
    return {
        onChange: (date, value) => dispatch(changeData(date, value)),
    };
};

const mapStateToProps = (state: IAccommodationStore, ownProps: IProps): IState => {
    const {accommodationItem} = ownProps;
    let value = null;
    if (state.inputConnector.hasOwnProperty(accommodationItem.date)) {
        value = state.inputConnector[accommodationItem.date];
    }
    return {
        value,
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(Single);

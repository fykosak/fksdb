import { lang } from '@i18n/i18n';
import { changeData } from '@inputConnector/actions';
import PriceDisplay from '@shared/components/displays/price';
import * as React from 'react';
import { connect } from 'react-redux';
import { Dispatch } from 'redux';
import {
    ScheduleGroupType,
    ScheduleItemDef,
} from '../middleware/interfaces';
import { Store } from '../reducer';
import CapacityLabel from './capacityLabel';
import { Params } from './index';

interface OwnProps {
    item: ScheduleItemDef;
    type: ScheduleGroupType;
    params: Params;
}

interface DispatchProps {
    onChange(value: number): void;
}

interface StateProps {
    value: number;
}

class ScheduleItem extends React.Component<OwnProps & DispatchProps & StateProps, {}> {

    public render() {
        const {item, value, onChange, params} = this.props;
        const {scheduleItemId, price, label, totalCapacity, usedCapacity, description} = item;

        const isChecked = (value === scheduleItemId);

        return <div className={'mb-3'}>
                <span className={'form-check ' + (isChecked ? 'text-success border-success' : '')}>
                <span
                    className={isChecked ? 'fa fa-check-square-o' : 'fa fa-square-o'}
                    onClick={() => {
                        isChecked ? onChange(null) : onChange(scheduleItemId);
                    }}
                />
                    <span className={'ml-3'}>{label} {params.displayDescription && description &&
                    <small>{description}</small>}</span>
            </span>
            <span className={'text-muted'}>
                {params.displayPrice &&
                <small className={'ml-3'}>{lang.getText('Price')}: <PriceDisplay price={price}/></small>}
                {params.displayCapacity && <CapacityLabel capacity={totalCapacity} usedCapacity={usedCapacity}/>}
            </span>
        </div>;
    }
}

const mapDispatchToProps = (dispatch: Dispatch, ownProps: OwnProps): DispatchProps => {
    return {
        onChange: (value: number) => dispatch(changeData(ownProps.item.scheduleGroupId.toString(), value)),
    };
};

const mapStateToProps = (state: Store, ownProps: OwnProps): StateProps => {
    const {item} = ownProps;
    let value = null;
    if (state.inputConnector.data.hasOwnProperty(item.scheduleGroupId.toString())) {
        value = state.inputConnector.data[item.scheduleGroupId.toString()];
    }
    return {
        value,
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(ScheduleItem);

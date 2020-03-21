import { lang } from '@i18n/i18n';
import { changeData } from '@inputConnector/actions';
import * as React from 'react';
import { connect } from 'react-redux';
import { Dispatch } from 'redux';
import {
    ScheduleGroupType,
    ScheduleItemDef,
} from '../middleware/interfaces';
import { Store } from '../reducer';
import { Params } from './index';
import CapacityLabel from './parts/capacityLabel';
import DescriptionLabel from './parts/descriptionLabel';
import PriceLabel from './parts/priceLabel';

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

class Item extends React.Component<OwnProps & DispatchProps & StateProps, {}> {

    public render() {
        const {item, value, onChange, params} = this.props;
        const {scheduleItemId, price, label, totalCapacity, usedCapacity, description} = item;
        const isChecked = (value === scheduleItemId);

        return <div className="mb-3">
                <span className={'form-check ' + (isChecked ? 'text-success border-success' : '')}>
                <span
                    className={isChecked ? 'fa fa-check-square-o' : 'fa fa-square-o'}
                    onClick={() => {
                        isChecked ? onChange(null) : onChange(scheduleItemId);
                    }}
                />
                    <span className="ml-3">
                        {label[lang.getCurrentLocale()]} {
                        params.display.description && <DescriptionLabel description={description}/>
                    }</span>
            </span>
            <span className="text-muted">
                {params.display.price && <PriceLabel price={price}/>}
                {params.display.capacity && <CapacityLabel capacity={totalCapacity} usedCapacity={usedCapacity}/>}
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

export default connect(mapStateToProps, mapDispatchToProps)(Item);

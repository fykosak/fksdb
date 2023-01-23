import { translator } from '@translator/translator';
import { Params } from 'FKSDB/Components/Forms/Controls/Schedule/ScheduleField';
import { changeData } from 'vendor/fykosak/nette-frontend-component/src/InputConnector/actions';
import { ScheduleGroupType } from 'FKSDB/Models/ORM/Models/Schedule/modelScheduleGroup';
import { ModelScheduleItem } from 'FKSDB/Models/ORM/Models/Schedule/modelScheduleItem';
import * as React from 'react';
import { connect } from 'react-redux';
import { Dispatch } from 'redux';
import { Store } from '../reducer';
import CapacityLabel from './Parts/CapacityLabel';
import DescriptionLabel from './Parts/DescriptionLabel';
import PriceLabel from './Parts/PriceLabel';

interface OwnProps {
    item: ModelScheduleItem;
    type: ScheduleGroupType;
    params: Params;
}

interface DispatchProps {
    onChange(value: number): void;
}

interface StateProps {
    value: number;
}

class Item extends React.Component<OwnProps & DispatchProps & StateProps> {

    public render() {
        const {item, value, onChange, params} = this.props;
        const {scheduleItemId, price, label, totalCapacity, usedCapacity, description} = item;
        const isChecked = (value === scheduleItemId);

        return <div className="mb-3">
                <span className={'form-check ' + (isChecked ? 'text-success border-success' : '')}>
                <span
                    className={isChecked ? 'fas fa-check-circle' : 'far fa-circle'}
                    onClick={() => {
                        isChecked ? onChange(null) : onChange(scheduleItemId);
                    }}
                />
                    <span className="ms-3">
                        {label[translator.getCurrentLocale()]} {
                        params.description && <DescriptionLabel description={description}/>
                    }</span>
            </span>
            <span className="text-muted">
                {params.price && <PriceLabel price={price}/>}
                {params.capacity && <CapacityLabel capacity={totalCapacity} usedCapacity={usedCapacity}/>}
            </span>
        </div>;
    }
}

const mapDispatchToProps = (dispatch: Dispatch): DispatchProps => {
    return {
        onChange: (value: number) => dispatch(changeData('data', value)),
    };
};

const mapStateToProps = (state: Store): StateProps => {
    return {
        value: state.inputConnector.data.data,
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(Item);

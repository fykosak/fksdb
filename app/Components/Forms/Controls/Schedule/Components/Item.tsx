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

        return <div
            className={'mb-3 card ' + (isChecked ? 'text-white bg-success' : '')}
            onClick={() => {
                isChecked ? onChange(null) : onChange(scheduleItemId);
            }}>
            <div className="card-body">
                <h5 className="card-title">
                    <i className={isChecked ? 'me-3 fas fa-check-circle' : 'me-3 far fa-circle'}/>
                    {label[translator.getCurrentLocale()]}
                </h5>
                {params.description && <h6 className="card-subtitle">
                    {description[translator.getCurrentLocale()]}
                </h6>}
                <p className="card-text">
                    {params.price && <PriceLabel price={price}/>}
                    {params.capacity && <CapacityLabel capacity={totalCapacity} usedCapacity={usedCapacity}/>}
                </p>
            </div>
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

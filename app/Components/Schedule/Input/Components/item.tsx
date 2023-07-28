import { Params } from 'FKSDB/Components/Schedule/Input/schedule-field';
import { changeData } from 'vendor/fykosak/nette-frontend-component/src/InputConnector/actions';
import { ScheduleGroupType } from 'FKSDB/Models/ORM/Models/Schedule/schedule-group-model';
import { ScheduleItemModel } from 'FKSDB/Models/ORM/Models/Schedule/schedule-item-model';
import * as React from 'react';
import { useContext } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { Store } from '../reducer';
import CapacityLabel from './capacity-label';
import PriceLabel from './price-label';
import { TranslatorContext } from '@translator/context';

interface OwnProps {
    item: ScheduleItemModel;
    type: ScheduleGroupType;
    params: Params;
}

export default function Item({item, params}: OwnProps) {

    const translator = useContext(TranslatorContext);
    const value = useSelector((state: Store) => state.inputConnector.data.data);
    const dispatch = useDispatch();
    const {scheduleItemId, price, label, totalCapacity, usedCapacity, description} = item;
    const isChecked = (value === scheduleItemId);

    return <div
        className={'mb-3 card ' + (isChecked ? 'text-white bg-success' : '')}
        onClick={() => {
            isChecked ? dispatch(changeData('data', null)) : dispatch(changeData('data', scheduleItemId));
        }}>
        <div className="card-body">
            <h5 className="card-title">
                <i className={isChecked ? 'me-3 fas fa-check-circle' : 'me-3 far fa-circle'}/>
                {translator.get(label)}
            </h5>
            {params.description && <h6 className="card-subtitle">
                {translator.get(description)}
            </h6>}
            <p className="card-text">
                {params.price && <PriceLabel price={price} translator={translator}/>}
                {params.capacity && <CapacityLabel capacity={totalCapacity} usedCapacity={usedCapacity}/>}
            </p>
        </div>
    </div>;
}

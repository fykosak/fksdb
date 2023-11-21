import {
    Params,
    renderOptions,
    ScheduleGroupModel,
    ScheduleGroupType,
} from 'FKSDB/Models/ORM/Models/Schedule/schedule-group-model';
import TimePrinter from 'FKSDB/Models/UI/time-printer';
import DateDisplay from 'FKSDB/Models/UI/date-printer';
import * as React from 'react';
import { useContext } from 'react';
import { TranslatorContext } from '@translator/context';
import { ScheduleItemModel } from 'FKSDB/Models/ORM/Models/Schedule/schedule-item-model';
import { useDispatch, useSelector } from 'react-redux';
import { Store } from 'FKSDB/Components/Schedule/Input/reducer';
import PriceLabel from 'FKSDB/Components/Schedule/Input/Components/price-label';
import CapacityLabel from 'FKSDB/Components/Schedule/Input/Components/capacity-label';
import { changeData } from 'vendor/fykosak/nette-frontend-component/src/InputConnector/actions';

interface OwnProps {
    group: ScheduleGroupModel;
}

export default function Group({group}: OwnProps) {
    const params = renderOptions(group.scheduleGroupType);
    const translator = useContext(TranslatorContext);
    return <div className="ms-3">
        <h5 className="mb-3">
            {translator.get(group.name)}
            {params.groupTime && (
                <small className="ms-3 text-muted">
                    <TimePrinter
                        date={group.start}
                        translator={translator}
                    /> - <TimePrinter
                    date={group.end}
                    translator={translator}
                />
                </small>)}
        </h5>
        {group.registrationEnd && <p className="alert alert-info">
            <i className="fas fa-info me-2"/>
            {translator.getText('Registration end: ')}
            <DateDisplay date={group.registrationEnd} translator={translator}/>
        </p>
        }
        <div className="row row-cols-1 row-cols-md-2 row-cols-lg-3">
            {group.items.map((item, index) => {
                return <div key={index} className="col">
                    <Item
                        params={params}
                        type={group.scheduleGroupType}
                        item={item}
                    />
                </div>;
            })}
        </div>
    </div>;
}

interface ItemProps {
    item: ScheduleItemModel;
    type: ScheduleGroupType;
    params: Params;
}

function Item({item, params}: ItemProps) {
    const translator = useContext(TranslatorContext);
    const value = useSelector((state: Store) => state.inputConnector.data.data);
    const dispatch = useDispatch();
    const {scheduleItemId, price, name, totalCapacity, usedCapacity, description} = item;
    const isChecked = (value === scheduleItemId);

    return <div
        className={'mb-3 card ' + (isChecked ? 'text-white bg-success' : '')}
        onClick={() => {
            isChecked ? dispatch(changeData('data', null)) : dispatch(changeData('data', scheduleItemId));
        }}>
        <div className="card-body">
            <h5 className="card-title">
                <i className={isChecked ? 'me-3 fas fa-check-circle' : 'me-3 far fa-circle'}/>
                {translator.get(name)}
            </h5>
            <h6 className="card-subtitle">
                {translator.get(description)}
            </h6>
            <p className="card-text">
                {params.price && <PriceLabel price={price} translator={translator}/>}
                {params.capacity && <CapacityLabel capacity={totalCapacity} usedCapacity={usedCapacity}/>}
            </p>
        </div>
    </div>;
}

import * as React from 'react';
import { Filter } from '../filter';
import { ACTION_SET_FILTER } from '../../actions/table';
import { useDispatch, useSelector } from 'react-redux';
import { Store } from 'FKSDB/Components/Game/ResultsAndStatistics/reducers/store';

interface OwnProps {
    filter: Filter;
}

export default function FilterComponent({filter}: OwnProps) {

    const dispatch = useDispatch();
    const activeFilter = useSelector((state: Store) => state.tableFilter.filter);
    const active = filter.same(activeFilter);
    return <a
        href="#"
        className={'btn ms-3 ' + (active ? 'btn-outline-success' : 'btn-outline-secondary')}
        onClick={() => dispatch({filter: active ? null : filter, type: ACTION_SET_FILTER})}
    >{filter.getHeadline()}</a>;
}

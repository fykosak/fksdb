import * as React from 'react';
import { useContext } from 'react';
import { useSelector } from 'react-redux';
import { createFilters } from '../filter';
import Filter from './filter';
import { Store } from 'FKSDB/Components/Game/ResultsAndStatistics/reducers/store';
import { TranslatorContext } from '@translator/context';

export default function FilterSelect() {
    const categories = useSelector((state: Store) => state.data.categories);
    const translator = useContext(TranslatorContext);
    return <>
        {createFilters(categories, translator).map((availableFilter, key) => {
            return <Filter key={key} filter={availableFilter}/>;
        })}
    </>;
}

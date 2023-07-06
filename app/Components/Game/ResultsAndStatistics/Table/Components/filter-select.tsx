import * as React from 'react';
import { useContext } from 'react';
import { connect } from 'react-redux';
import { createFilters } from '../filter';
import Filter from './filter';
import { Store } from 'FKSDB/Components/Game/ResultsAndStatistics/reducers/store';
import { TranslatorContext } from '@translator/context';

interface StateProps {
    categories: string[];
}

function FilterSelect(props: StateProps) {
    const {categories} = props;
    const translator = useContext(TranslatorContext);
    return <>
        {createFilters(categories, translator).map((availableFilter, key) => {
            return <Filter key={key} filter={availableFilter}/>;
        })}
    </>;
}

const mapStateToPros = (state: Store): StateProps => {
    return {
        categories: state.data.categories,
    };
};

export default connect(
    mapStateToPros,
    null,
)(FilterSelect);

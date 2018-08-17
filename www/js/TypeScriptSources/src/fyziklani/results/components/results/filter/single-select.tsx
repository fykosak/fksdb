import * as React from 'react';
import {
    connect,
    Dispatch,
} from 'react-redux';
import { setFilter } from '../../../actions/table-filter';
import { IFyziklaniResultsStore } from '../../../reducers';
import { Filter } from './filter';
import FilterComponent from './filter-component';
import { createFilters } from './filters';

interface IState {
    filters?: Filter[];
    categories?: string[];
    onSetFilter?: (filter: Filter) => void;
}

class Select extends React.Component<IState, {}> {

    public render() {
        const {categories, filters, onSetFilter} = this.props;
        const availableFilters = createFilters([], categories);

        return <>
            {availableFilters.map((filter, key) => {
                const active = filters.some((activeFilters) => {
                    return filter.same(activeFilters);
                });
                return <FilterComponent key={key} filter={filter} active={active} onClick={onSetFilter}/>;

            })}
        </>;

    }
}

const mapDispatchToProps = (dispatch: Dispatch<IFyziklaniResultsStore>): IState => {
    return {
        onSetFilter: (filter: Filter) => dispatch(setFilter(filter)),
    };
};
const mapStateToPros = (state: IFyziklaniResultsStore): IState => {
    return {
        categories: state.data.categories,
        filters: state.tableFilter.filters,
    };
};

export default connect(
    mapStateToPros,
    mapDispatchToProps,
)(Select);

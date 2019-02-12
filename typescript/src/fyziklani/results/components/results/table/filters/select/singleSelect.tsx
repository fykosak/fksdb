import * as React from 'react';
import { connect } from 'react-redux';
import {
    Action,
    Dispatch,
} from 'redux';
import HardVisibleSwitch from '../../../../../../helpers/options/compoents/hard-visible-switch';
import { setFilter } from '../../../../../actions/table-filter';
import { Filter } from '../../../../../middleware/results/filters/filter';
import FilterComponent from '../../../../../middleware/results/filters/FilterComponent';
import { createFilters } from '../../../../../middleware/results/filters/filters';
import { FyziklaniResultsStore } from '../../../../../reducers';

interface State {
    filters?: Filter[];
    categories?: string[];
    isOrg?: boolean;

    onSetFilter?(filter: Filter): void;
}

class SingleSelect extends React.Component<State, {}> {

    public render() {
        const {categories, filters, onSetFilter, isOrg} = this.props;
        const availableFilters = createFilters([], categories, false);

        return <>
            {isOrg && <HardVisibleSwitch/>}
            {availableFilters.map((filter, key) => {
                const active = filters.some((activeFilters) => {
                    return filter.same(activeFilters);
                });
                return <FilterComponent key={key} filter={filter} active={active} onClick={onSetFilter}/>;

            })}
        </>;
    }
}

const mapDispatchToProps = (dispatch: Dispatch<Action<string>>): State => {
    return {
        onSetFilter: (filter: Filter) => dispatch(setFilter(filter)),
    };
};
const mapStateToPros = (state: FyziklaniResultsStore): State => {
    return {
        categories: state.data.categories,
        filters: state.tableFilter.filters,
        isOrg: state.options.isOrg,
    };
};

export default connect(
    mapStateToPros,
    mapDispatchToProps,
)(SingleSelect);

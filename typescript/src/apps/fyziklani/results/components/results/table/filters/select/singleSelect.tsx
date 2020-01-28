import * as React from 'react';
import { connect } from 'react-redux';
import {
    Action,
    Dispatch,
} from 'redux';
import HardVisibleSwitch from '../../../../../../options/compoents/hardVisibleSwitch';
import { setFilter } from '../../../../../actions/tableFilter';
import { Filter } from '../../../../../middleware/results/filters/filter';
import FilterComponent from '../../../../../middleware/results/filters/filterComponent';
import { createFilters } from '../../../../../middleware/results/filters/filters';
import { FyziklaniResultsStore } from '../../../../../reducers';

interface StateProps {
    filters: Filter[];
    categories: string[];
    isOrg: boolean;
}

interface DispatchProps {
    onSetFilter(filter: Filter): void;
}

class SingleSelect extends React.Component<StateProps & DispatchProps, {}> {

    public render() {
        const {categories, filters, onSetFilter, isOrg} = this.props;
        const availableFilters = createFilters(categories, false);

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

const mapDispatchToProps = (dispatch: Dispatch<Action<string>>): DispatchProps => {
    return {
        onSetFilter: (filter: Filter) => dispatch(setFilter(filter)),
    };
};
const mapStateToPros = (state: FyziklaniResultsStore): StateProps => {
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

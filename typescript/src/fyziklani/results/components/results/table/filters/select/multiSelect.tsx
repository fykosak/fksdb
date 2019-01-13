import * as React from 'react';
import { connect } from 'react-redux';
import {
    Action,
    Dispatch,
} from 'redux';
import { lang } from '../../../../../../../i18n/i18n';
import { Room } from '../../../../../../helpers/interfaces';
import {
    addFilter,
    removeFilter,
} from '../../../../../actions/table-filter';
import { Filter } from '../../../../../middleware/results/filters/filter';
import FilterComponent from '../../../../../middleware/results/filters/filter-component';
import { createFilters } from '../../../../../middleware/results/filters/filters';
import { FyziklaniResultsStore } from '../../../../../reducers';

interface State {
    filters?: Filter[];
    index?: number;
    categories?: string[];
    rooms?: Room[];

    onAddFilter?(filter: Filter): void;

    onRemoveFilter?(filter: Filter): void;
}

class MultiSelect extends React.Component<State, {}> {

    public render() {
        const {categories, filters, index, rooms, onRemoveFilter, onAddFilter} = this.props;
        const availableFilters = createFilters(rooms, categories);

        return <>
            <h5 className="text-success">{lang.getText('Active filters')}</h5>
            <div>
                {filters.map((filter, key) => {
                    return <FilterComponent
                        filter={filter}
                        onCloseClick={onRemoveFilter}
                        active={(key === index)}
                    />;
                })}
            </div>
            <hr/>
            <h5>{lang.getText('Available filters')}</h5>

            <div>
                {availableFilters.filter((filter) => {
                    return !filters.some((activeFilters) => {
                        return filter.same(activeFilters);
                    });
                }).map((filter, key) => {
                    return <FilterComponent
                        key={key}
                        filter={filter}
                        onClick={onAddFilter}
                        active={false}
                        type={'primary'}
                    />;

                })}
            </div>
        </>;
    }
}

const mapDispatchToProps = (dispatch: Dispatch<Action<string>>): State => {
    return {
        onAddFilter: (filter: Filter) => dispatch(addFilter(filter)),
        onRemoveFilter: (filter: Filter) => dispatch(removeFilter(filter)),
    };
};
const mapStateToPros = (state: FyziklaniResultsStore): State => {
    return {
        categories: state.data.categories,
        filters: state.tableFilter.filters,
        index: state.tableFilter.index,
        rooms: state.data.rooms,
    };
};

export default connect(
    mapStateToPros,
    mapDispatchToProps,
)(MultiSelect);

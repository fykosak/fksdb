import { translator } from '@translator/Translator';
import * as React from 'react';
import { connect } from 'react-redux';
import {
    Action,
    Dispatch,
} from 'redux';
import { FyziklaniResultsTableStore } from '../../../../ResultsTable/reducers';
import {
    addFilter,
    removeFilter,
} from '../../../Actions/tableFilter';
import { createFilters, Filter } from '../../../Filter';
import FilterComponent from '../filterComponent';

interface StateProps {
    filters: Filter[];
    index: number;
    categories: string[];
}

interface DispatchProps {
    onAddFilter(filter: Filter): void;

    onRemoveFilter(filter: Filter): void;
}

class MultiSelect extends React.Component<StateProps & DispatchProps, {}> {

    public render() {
        const {categories, filters, index, onRemoveFilter, onAddFilter} = this.props;
        const availableFilters = createFilters(categories);

        return <>
            <h5 className="text-success">{translator.getText('Active filters')}</h5>
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
            <h5>{translator.getText('Available filters')}</h5>

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

const mapDispatchToProps = (dispatch: Dispatch<Action<string>>): DispatchProps => {
    return {
        onAddFilter: (filter: Filter) => dispatch(addFilter(filter)),
        onRemoveFilter: (filter: Filter) => dispatch(removeFilter(filter)),
    };
};
const mapStateToPros = (state: FyziklaniResultsTableStore): StateProps => {
    return {
        categories: state.data.categories,
        filters: state.tableFilter.filters,
        index: state.tableFilter.index,
    };
};

export default connect(
    mapStateToPros,
    mapDispatchToProps,
)(MultiSelect);

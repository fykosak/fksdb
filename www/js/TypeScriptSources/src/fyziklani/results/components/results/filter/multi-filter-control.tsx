import * as React from 'react';
import {
    connect,
    Dispatch,
} from 'react-redux';
import { lang } from '../../../../../i18n/i18n';
import { IRoom } from '../../../../helpers/interfaces';
import {
    addFilter,
    removeFilter,
} from '../../../actions/table-filter';
import { IFyziklaniResultsStore } from '../../../reducers';
import { Filter } from './filter';
import FilterComponent from './filter-component';
import { createFilters } from './filters';

interface IState {
    filters?: Filter[];
    index?: number;
    categories?: string[];
    rooms?: IRoom[];

    onAddFilter?(filter: Filter): void;

    onRemoveFilter?(filter: Filter): void;
}

class MultiFilterControl extends React.Component<IState, {}> {

    public render() {
        const {categories, filters, index, rooms,  onRemoveFilter, onAddFilter} = this.props;
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

const mapDispatchToProps = (dispatch: Dispatch<IFyziklaniResultsStore>): IState => {
    return {
        onAddFilter: (filter: Filter) => dispatch(addFilter(filter)),
        onRemoveFilter: (filter: Filter) => dispatch(removeFilter(filter)),
    };
};
const mapStateToPros = (state: IFyziklaniResultsStore): IState => {
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
)(MultiFilterControl);

import * as React from 'react';
import {
    connect,
    Dispatch,
} from 'react-redux';
import { IRoom } from '../../../../helpers/interfaces';
import {
    addFilter,
    removeFilter,
} from '../../../actions/table-filter';
import { IFyziklaniResultsStore } from '../../../reducers';
import AutoSwitchControl from './auto-switch-control';
import { Filter } from './filter';
import FilterComponent from './filter-component';
import { createFilters } from './filters';

interface IState {
    filters?: Filter[];
    index?: number;
    categories?: string[];
    onAddFilter?: (filter: Filter) => void;
    onRemoveFilter?: (filter: Filter) => void;
    autoSwitch?: boolean;
    rooms?: IRoom[];
}

class Select extends React.Component<IState, {}> {

    public render() {
        const {categories, filters, index, rooms,autoSwitch,onRemoveFilter,onAddFilter} = this.props;
        const availableFilters = createFilters(rooms, categories);

        return <div className="form-group">
            <button type="button" className="btn btn-primary" data-toggle="modal" data-target="#fyziklaniResultsOptionModal">
                <i className="fa fa-gear"/>
            </button>
            <div className="modal fade" id="fyziklaniResultsOptionModal" tabIndex={-1} role="dialog">
                <div className="modal-dialog" role="document">
                    <div className="modal-content">
                        <div className="modal-header">
                            <h5 className="modal-title">Options</h5>
                            <button type="button" className="close" data-dismiss="modal">
                                <span>&times;</span>
                            </button>
                        </div>
                        <div className="modal-body">
                            <AutoSwitchControl/>
                            <hr/>
                            {autoSwitch ? (
                                <>
                                    <h5 className="text-success">Aktívne filtre</h5>
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
                                    <h5>Dostupné filtre</h5>

                                    <div>
                                        {availableFilters.filter((filter) => {
                                            return !filters.some((activeFilters) => {
                                                return filter.same(activeFilters);
                                            });
                                        }).map((filter, key) => {
                                            return <FilterComponent
                                                filter={filter}
                                                onClick={onAddFilter}
                                                active={false}
                                            />;

                                        })}
                                    </div>
                                </>
                            ) : (null)}
                        </div>
                    </div>
                </div>
            </div>
        </div>
            ;
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
        autoSwitch: state.tableFilter.autoSwitch,
        categories: state.data.categories,
        filters: state.tableFilter.filters,
        index: state.tableFilter.index,
        rooms: state.data.rooms,
    };
};

export default connect(
    mapStateToPros,
    mapDispatchToProps,
)(Select);

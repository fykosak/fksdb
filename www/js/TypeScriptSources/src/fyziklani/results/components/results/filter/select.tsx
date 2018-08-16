import * as React from 'react';
import {
    connect,
    Dispatch,
} from 'react-redux';
import { Filter } from '../../../../../results/helpers/filters/filters';
import { IRoom } from '../../../../helpers/interfaces';
import {
    addFilter,
    removeFilter,
    setAutoSwitch,
} from '../../../actions/table-filter';
import { IFyziklaniResultsStore } from '../../../reducers';
import AutoSwitchControl from './auto-switch-control';
import { createFilters } from './filters';

interface IState {
    autoSwitch?: boolean;
    filters?: Filter[];
    index?: number;
    rooms?: IRoom[];
    categories?: string[];
    onAddFilter?: (filter: Filter) => void;
    onRemoveFilter?: (filter: Filter) => void;
    onAutoSwitch?: (state: boolean) => void;
}

class Select extends React.Component<IState, {}> {

    public render() {
        const {rooms, categories, filters, index} = this.props;
        const availableFilters = createFilters(rooms, categories);

        return <>
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
                            <h5 className="text-success">Aktívne filtre</h5>
                            <div>
                                {filters.map((filter, key) => {
                                    return <span
                                        className={'btn ' + (key === index ? 'btn-primary' : 'btn-secondary')}

                                        key={key}
                                    >{filter.getHeadline()}
                                        <span className="ml-3" onClick={() => {
                                            this.props.onRemoveFilter(filter);
                                        }}>&times;</span></span>;
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
                                    return <button
                                        className={'btn btn-success'}
                                        onClick={() => {
                                            this.props.onAddFilter(filter);
                                        }}
                                        key={key}
                                    >{filter.getHeadline()}</button>;
                                })}
                            </div>
                            <hr/>
                            <AutoSwitchControl/>
                        </div>
                    </div>
                </div>
            </div>
        </>
            ;
    }
}

const mapDispatchToProps = (dispatch: Dispatch<IFyziklaniResultsStore>): IState => {
    return {
        onAddFilter: (filter: Filter) => dispatch(addFilter(filter)),
        onAutoSwitch: (state) => dispatch(setAutoSwitch(state)),
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

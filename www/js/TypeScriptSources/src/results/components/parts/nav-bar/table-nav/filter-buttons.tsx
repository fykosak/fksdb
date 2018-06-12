import * as React from 'react';
import {
    connect,
    Dispatch,
} from 'react-redux';
import Lang from '../../../../../lang/components/lang';
import { setUserFilter } from '../../../../actions/table-filter';
import { Filter } from '../../../../helpers/filters/filters';
import { IStore } from '../../../../reducers';

interface IState {
    filters?: Filter[];
    onFilterChange?: (filter: Filter) => void;
    userFilter?: Filter;
}

class FiltersButtons extends React.Component<IState, {}> {

    public render() {
        const {onFilterChange, userFilter, filters} = this.props;
        const filtersButtons = filters.map((filter, index) => {
            return (
                <div key={index} className={'dropdown-item ' + (filter.same(userFilter) ? ' active' : '')}
                     onClick={() => onFilterChange(filter)}>
                    {filter.name}
                </div>
            );
        });
        return (
            <div className="list-group-item dropdown">
                <button type="button"
                        className={'btn dropdown-toggle btn-secondary'}
                        data-toggle="dropdown"
                >
                    <span className="fa-filter fa"/>
                    <Lang text={'filters'}/>
                </button>
                <div className="dropdown-menu">
                    <div className={'dropdown-item '}
                         onClick={() => onFilterChange(null)}
                    > Auto
                    </div>
                    {filtersButtons}
                </div>
            </div>

        );
    }
}

const mapStateToProps = (state: IStore): IState => {
    return {
        filters: state.tableFilter.filters,
        userFilter: state.tableFilter.userFilter,
    };
};

const mapDispatchToProps = (dispatch: Dispatch<IStore>): IState => {
    return {
        onFilterChange: (filter: Filter) => dispatch(setUserFilter(filter, 'table')),
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(FiltersButtons);

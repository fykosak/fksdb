import * as React from 'react';
import {
    connect,
    Dispatch,
} from 'react-redux';

import {
    Filter,
    filters,
} from '../../../helpers/filters/filters';

import { setUserFilter } from '../../../actions/table-filter';
import { IStore } from '../../../reducers/index';

interface IState {
    onFilterChange?: (filter: Filter) => void;
    userFilter?: Filter;
}

class FiltersButtons extends React.Component<IState, {}> {

    public render() {
        const { onFilterChange, userFilter } = this.props;
        const filtersButtons = filters.map((filter, index) => {
            return (
                <li key={index} className="nav-item" role="presentation">
                    <a className={'nav-link' + (filter.same(userFilter) ? ' active' : '')} href="#" onClick={() => onFilterChange(filter)}>
                        {filter.name}
                    </a>
                </li>
            );
        });

        return (
            <ul className="nav nav-tabs">
                <li className="nav-item" role="presentation">
                    <a className={'nav-link' + (!userFilter ? ' active' : '')} href="#" onClick={() => onFilterChange(null)}>
                        Auto
                    </a>
                </li>
                {filtersButtons}
            </ul>
        );
    }
}

const mapStateToProps = (state: IStore): IState => {
    return {
        userFilter: state.tableFilter.userFilter,
    };
};

const mapDispatchToProps = (dispatch: Dispatch<IStore>): IState => {
    return {
        onFilterChange: (filter: Filter) => dispatch(setUserFilter(filter)),
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(FiltersButtons);

import * as React from 'react';
import {connect} from 'react-redux';

import {
    Filter,
    filters,
} from '../../../helpers/filters/filters';

import {setUserFilter} from '../../../actions/table-filter';

interface IProps {
    onFilterChange?: Function;
    userFilter?: Filter;
}

class FiltersButtons extends React.Component<IProps, void> {

    public render() {
        const {onFilterChange, userFilter} = this.props;
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

const mapStateToProps = (state, ownProps) => {
    return {
        ...ownProps,
        isReady: state.options.isReady,
        userFilter: state.tableFilter.userFilter,
    };
};

const mapDispatchToProps = (dispatch, ownProps) => {
    return {
        ...ownProps,
        onFilterChange: (filter: Filter) => dispatch(setUserFilter(filter)),
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(FiltersButtons);

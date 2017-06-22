import * as React from 'react';
import {connect} from 'react-redux';

import {
    Filter,
    filters,
} from '../../helpers/filters/filters';
import {changeFilter} from '../../actions/options';

interface IProps {
    onFilterChange?: Function;
}

class FiltersButtons extends React.Component<IProps, void> {

    public render() {
        const {onFilterChange} = this.props;
        const filtersButtons = filters.map((filter, index) => {
            return (
                <li key={index} role="presentation">
                    <a href="#" onClick={() => onFilterChange()}>
                        {filter.name}
                    </a>
                </li>
            );
        });

        return (
            <ul className="nav nav-tabs">
                {filtersButtons}
            </ul>
        );
    }
}

const mapStateToProps = (state, ownProps) => {
    return {
        ...ownProps,
        isReady: state.options.isReady,
    };
};

const mapDispatchToProps = (dispatch, ownProps) => {
    return {
        ...ownProps,
        onFilterChange: (filter: Filter) => dispatch(changeFilter(filter)),
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(FiltersButtons);

import * as React from 'react';
import {connect} from 'react-redux';

import {filters} from '../../helpers/filters';

class Results extends React.Component<any, void> {

    public render() {

        const filtersButtons = filters.map((filter, index) => {
            return (
                <li key={index} role="presentation">
                    <a href="#">
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
    }
};

const mapDispatchToProps = (dispatch, ownProps) => {
    return {
        ...ownProps,
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(Results);

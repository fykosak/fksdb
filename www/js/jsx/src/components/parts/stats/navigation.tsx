import * as React from 'react';
import {connect} from 'react-redux';
import {changeSubPage} from '../../../actions/options';

interface IProps {
    subPage: string;
    onchangeSupPage: Function;
}

class Navigation extends React.Component<IProps, void> {

    render() {
        const {subPage, onchangeSupPage} = this.props;
        return (
            <div className="container">
                <ul className="nav nav-pills nav-fill mb-3">
                    <li className="nav-item" onClick={() => onchangeSupPage('teams')}>
                        <a
                            className={'nav-link ' + ((!subPage || subPage === 'teams') ? 'active' : '')}
                            href="#">Teams</a>
                    </li>
                    <li className="nav-item" onClick={() => onchangeSupPage('task')}>
                        <a
                            className={'nav-link ' + (subPage === 'task' ? 'active' : '')}
                            href="#">task</a>
                    </li>

                </ul>
            </div>
        );
    }
}

const mapStateToProps = (state, ownProps) => {
    return {
        ...ownProps,
        subPage: state.options.subPage,
    };
};

const mapDispatchToProps = (dispatch, ownProps) => {
    return {
        ...ownProps,
        onchangeSupPage: (subPage) => dispatch(changeSubPage(subPage))
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(Navigation);

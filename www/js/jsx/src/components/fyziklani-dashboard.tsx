import * as React from 'react';
import {connect} from 'react-redux';

import Results from './containers/results';
import Statistics from './containers/statistics';
import {basePath} from '../helpers/base-path';
import {changePage} from '../actions/options';

interface IProps {
    page?: string;
    isReady?: boolean;
    onchangePage?: Function;
}

class FyziklaniDashboard extends React.Component<IProps, void> {

    public render() {
        const {isReady, page, onchangePage} = this.props;

        if (!isReady) {
            return (
                <div className="load" style={{textAlign: 'center',}}>
                    <img src={basePath + '/images/gears.svg'} style={{width: '50%'}}/>
                </div>)
        }
        const navbar = ( <ul className="nav nav-tabs nav-fill mb-3">
            <li className="nav-item" onClick={() => onchangePage('results')}>
                <a
                    className={'nav-link ' + ((!page || page === 'results') ? 'active' : '')}
                    href="#">Results</a>
            </li>
            <li className="nav-item" onClick={() => onchangePage('stats')}>
                <a
                    className={'nav-link ' + (page === 'stats' ? 'active' : '')}
                    href="#">Statistics</a>
            </li>

        </ul>);
        switch (page) {
            case 'stats':
                return (
                    <div>
                        {navbar}
                        <Statistics/>
                    </div>
                );
            default:
            case 'results':
                return (
                    <div>
                        {navbar}
                        <Results/>
                    </div>
                );
        }
    };
}

const mapStateToProps = (state, ownProps): IProps => {
    return {
        ...ownProps,
        isReady: state.options.isReady,
        page: state.options.page,
    };
};

const mapDispatchToProps = (dispatch, ownProps) => {
    return {
        ...ownProps,
        onchangePage: (page) => dispatch(changePage(page))
    };
};

export default connect(
    mapStateToProps,
    mapDispatchToProps,
)(FyziklaniDashboard);

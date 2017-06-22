import * as React from 'react';
import {connect} from 'react-redux';

import Results from './parts/table/index';
import Statistics from './parts/stats/index';
import {basePath} from '../helpers/base-path';
import {changePage} from '../actions/options';
import NavBar from './parts/nav-bar/index';

interface IProps {
    page?: string;
    isReady?: boolean;
}

class FyziklaniDashboard extends React.Component<IProps, void> {

    public render() {
        const {isReady, page} = this.props;

        if (!isReady) {
            return (
                <div className="load" style={{textAlign: 'center',}}>
                    <img src={basePath + '/images/gears.svg'} style={{width: '50%'}}/>
                </div>)
        }

        switch (page) {
            case 'stats':
                return (
                    <div>
                        <NavBar/>
                        <Statistics/>
                    </div>
                );
            case 'table':
                return (
                    <div>
                        <NavBar/>
                        <Results/>
                    </div>
                );
            default:
                return (
                    <div>
                        <NavBar/>
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

export default connect(
    mapStateToProps,
    null,
)(FyziklaniDashboard);

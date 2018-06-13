import * as React from 'react';
import { connect } from 'react-redux';
import { IStore } from '../reducers/';
import NavBar from './parts/nav-bar/index';
import Statistics from './parts/stats/index';
import Results from './parts/table/index';

interface IState {
    page?: string;
    isReady?: boolean;
}

interface IProps {
    basePath: string;
}

class BrawlDashboard extends React.Component<IState & IProps, {}> {

    public render() {
        const { isReady, page, basePath } = this.props;

        if (!isReady) {
            return (
                <div className="load" style={{ textAlign: 'center' }}>
                    <img src={basePath + 'images/gears.svg'} style={{ width: '50%' }}/>
                </div>);
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
                        <Results basePath={basePath}/>
                    </div>
                );
            default:
                return (
                    <div>
                        <NavBar/>
                    </div>
                );
        }
    }
}

const mapStateToProps = (state: IStore): IState => {
    return {
        isReady: state.options.isReady,
        page: state.options.page,
    };
};

export default connect(
    mapStateToProps,
    (): IState => {
        return {};
    },
)(BrawlDashboard);

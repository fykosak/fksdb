import * as React from 'react';
import {
    connect,
    Dispatch,
} from 'react-redux';

import Lang from '../../../../lang/components/lang';
import Card from '../../../../shared/components/card';
import {
    changePage,
} from '../../../actions/options';
import { IStore } from '../../../reducers';
import StatsNav from './stats-nav';
import TableNav from './table-nav/index';

interface IState {
    onChangePage?: (page: string) => void;
    page?: string;
    autoSwitch?: boolean;
}

class NavBar extends React.Component<IState, { display: boolean }> {

    public constructor(props) {
        super(props);
        this.state = {
            display: true,
        };
    }

    public render() {
        return (<div>
            <button className={'btn btn-secondary ' + (this.state.display ? 'active' : '')}
                    onClick={() => this.setState({display: !this.state.display})}>
                <span className="fa fa-gear"/>
            </button>
            <div className="col-lg-3 col-md-4 col-ms-12" style={{
                display: this.state.display ? 'block' : 'none',
                position: 'absolute',
                zIndex: 100,
            }}>
                <Card headline={<Lang text={'options'}/>} level="info">
                    <nav className="list-group list-group-flush">
                        <StatsNav/>
                        <TableNav/>
                    </nav>
                </Card>
            </div>
        </div>);
    }
}

const mapStateToProps = (state: IStore): IState => {
    return {
        autoSwitch: state.tableFilter.autoSwitch,
        page: state.options.page,
    };
};

const mapDispatchToProps = (dispatch: Dispatch<IStore>): IState => {
    return {
        onChangePage: (page) => dispatch(changePage(page)),
    };
};

export default connect(
    mapStateToProps,
    mapDispatchToProps,
)(NavBar);

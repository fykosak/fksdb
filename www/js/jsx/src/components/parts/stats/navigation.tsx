import * as React from 'react';
import {
    connect,
    Dispatch,
} from 'react-redux';
import { changeSubPage } from '../../../actions/options';
import { IStore } from '../../../reducers/index';

interface IState {
    subPage?: string;
    onchangeSupPage?: (subPage: string) => void;
}

class Navigation extends React.Component<IState, {}> {

    public render() {
        const { subPage, onchangeSupPage } = this.props;
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
                            href="#">Task</a>
                    </li>
                </ul>
            </div>
        );
    }
}

const mapStateToProps = (state: IStore): IState => {
    return {
        subPage: state.options.subPage,
    };
};

const mapDispatchToProps = (dispatch: Dispatch<IStore>): IState => {
    return {
        onchangeSupPage: (subPage) => dispatch(changeSubPage(subPage)),
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(Navigation);

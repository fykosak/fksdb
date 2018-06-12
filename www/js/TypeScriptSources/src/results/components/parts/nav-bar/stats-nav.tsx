import * as React from 'react';
import {
    connect,
    Dispatch,
} from 'react-redux';
import Lang from '../../../../lang/components/lang';
import { changeSubPage } from '../../../actions/options';
import { IStore } from '../../../reducers';

interface IState {
    page?: string;
    subPage?: string;
    onchangeSupPage?: (subPage: string) => void;
}

class StatsNav extends React.Component<IState, {}> {

    public render() {
        const {subPage, onchangeSupPage, page} = this.props;
        return (
            <div className="list-group-item dropdown">
                <button type="button"
                        className={'btn dropdown-toggle ' + ((page === 'stats') ? 'btn-success' : 'btn-secondary')}
                        data-toggle="dropdown"
                >
                    <span className="fa fa-bar-chart"/>
                    <Lang text={'statistics'}/>
                </button>
                <div className="dropdown-menu">
                    <div
                        className={'dropdown-item ' + ((!subPage || subPage === 'teams') ? 'active' : '')}
                        onClick={() => onchangeSupPage('teams')}
                    >Teams
                    </div>
                    <div
                        className={'dropdown-item ' + (subPage === 'task' ? 'active' : '')}
                        onClick={() => onchangeSupPage('task')}
                    >Task
                    </div>
                </div>
            </div>

        );
    }
}

const mapStateToProps = (state: IStore): IState => {
    return {
        page: state.options.page,
        subPage: state.options.subPage,
    };
};

const mapDispatchToProps = (dispatch: Dispatch<IStore>): IState => {
    return {
        onchangeSupPage: (subPage) => dispatch(changeSubPage('stats', subPage)),
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(StatsNav);

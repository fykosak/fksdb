import * as React from 'react';
import {
    connect,
    Dispatch,
} from 'react-redux';
import Lang from '../../../../../lang/components/lang';
import { changePage } from '../../../../actions/options';
import { IStore } from '../../../../reducers';
import AutoSwitchCheck from './auto-switch-check';
import FilterButtons from './filter-buttons';
import IsOrgCheck from './is-org-check';
import Options from './options';

interface IState {
    onChangePage?: (page: string) => void;
    page?: string;
    autoSwitch?: boolean;
}

class TableNav extends React.Component<IState, {}> {

    public render() {
        const {page, onChangePage, autoSwitch} = this.props;

        return (<div>
            <div className="list-group-item">
                <button type="button"
                        className={'btn ' + ((page === 'table') ? 'btn-success' : 'btn-secondary')}
                        onClick={() => onChangePage('table')}
                >
                    <span className="fa-tasks fa"/>
                    <Lang text={'table'}/>
                </button>
            </div>
            {(page === 'table') && (
                <div className="list-group-item">
                    <AutoSwitchCheck/>
                    <IsOrgCheck/>
                    {autoSwitch ? (<Options/>) : (<FilterButtons/>)}
                </div>
            )}
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
)(TableNav);

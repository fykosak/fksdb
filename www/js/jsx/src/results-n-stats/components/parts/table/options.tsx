import * as React from 'react';

import {
    connect,
    Dispatch,
} from 'react-redux';

import { setHardVisible } from '../../../actions/options';
import {
    setCategory,
    setRoom,
} from '../../../actions/table-filter';

import { filters } from '../../../helpers/filters/filters';
import { IStore } from '../../../reducers/index';

interface IComponentState {
    isDisplayed: boolean;
}
interface IState {
    onRoomChange?: (room: string) => void;
    onCategoryChange?: (category: string) => void;
    onHardDisplayChange?: (status: boolean) => void;
    isOrg?: boolean;
}

class Options extends React.Component<IState, IComponentState> {
    constructor() {
        super();
        this.state = {
            isDisplayed: false,
        };
    }

    public render() {
        const {
            onRoomChange,
            onCategoryChange,
            onHardDisplayChange,
            isOrg,
        } = this.props;
        const { isDisplayed } = this.state;
        return (
            <div>
                <button
                    className={'btn btn-secondary ' + (isDisplayed ? 'active' : '')}
                    onClick={() => this.setState({ isDisplayed: !isDisplayed })}
                >
                    Nastavení
                </button >

                <div style={{ display: isDisplayed ? 'block' : 'none' }}>
                    <div className="form-group">
                        <label className="sr-only">
                            <span>Místnost</span>
                        </label>
                        <select
                            className="form-control"
                            onChange={(event) => onRoomChange(event.target.value)}>
                            {                                filters
                                .filter((filter) => filter.room !== null)
                                .map((filter, index) => {
                                    return (<option key={index} value={filter.room}>{filter.name}</option>);
                                })
                            }
                        </select>
                    </div>

                    <div className="form-group">
                        <label className="sr-only">
                            <span>Kategorie</span>
                        </label>
                        <select
                            className="form-control"
                            onChange={(event) => onCategoryChange(event.target.value)}>

                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                        </select>
                    </div>
                    <div className="form-group has-error">
                        <div className="checkbox">
                            <label>
                                <input type="checkbox" disabled={!isOrg} value="1"
                                       onChange={(event) => onHardDisplayChange(event.target.checked)}/>Neveřejné výsledkovky, <span
                                className="text-danger">tuto funkci nezapínejte pokud jsou výsledkovky promítané!!!</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        );
    }
}

const mapStateToProps = (state: IStore): IState => {
    return {
        isOrg: state.options.isOrg,
    };
};

const mapDispatchToProps = (dispatch: Dispatch<IStore>): IState => {
    return {
        onCategoryChange: (category: string) => dispatch(setCategory(category)),
        onHardDisplayChange: (status: boolean) => dispatch(setHardVisible(status)),
        onRoomChange: (room: string) => dispatch(setRoom(room)),
    };
};

export default connect(
    mapStateToProps,
    mapDispatchToProps,
)(Options);

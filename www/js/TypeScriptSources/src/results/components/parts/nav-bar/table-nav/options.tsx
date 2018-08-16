import * as React from 'react';

import {
    connect,
    Dispatch,
} from 'react-redux';
import {
    setCategory,
    setRoom,
} from '../../../../../fyziklani/results/actions/table-filter';

import {
    Filter,
} from '../../../../helpers/filters/filters';
import { IStore } from '../../../../reducers';

interface IState {
    filters?: Filter[];
    onRoomChange?: (room: number) => void;
    onCategoryChange?: (category: string) => void;
    isOrg?: boolean;
    selectedCategory?: string;
    selectedRoomId?: number;
}

const categories = ['A', 'B', 'C', 'F'];

class Options extends React.Component<IState, {}> {

    public render() {
        const {
            filters,
            onRoomChange,
            onCategoryChange,
            selectedCategory,
            selectedRoomId,

        } = this.props;
        return (
            <div className="form-horizontal">
                <h6>Table settings</h6>
                <div className="form-group">
                    <label className="sr-only">
                        <span>Room</span>
                    </label>
                    <select
                        className="form-control"
                        onChange={(event) => +event.target.value ? onRoomChange(+event.target.value) : null}>
                        <option value="0">--</option>
                        {filters
                            .filter((filter) => filter.roomId !== null)
                            .map((filter, index) => {
                                return (<option
                                    key={index}
                                    value={filter.roomId}
                                    selected={filter.roomId === selectedRoomId}>{filter.name}</option>);
                            })
                        }
                    </select>
                </div>

                <div className="form-group">
                    <label className="sr-only">
                        <span>Category</span>
                    </label>
                    <select
                        className="form-control"
                        onChange={(event) => (event.target.value !== "0") ? onCategoryChange(event.target.value) : null}>
                        <option value="0">--</option>
                        {categories.map((category, index) => {
                            return (<option value={category} key={index} selected={category === selectedCategory}>{category}</option>);
                        })}
                    </select>
                </div>
            </div>

        );
    }
}

const mapStateToProps = (state: IStore): IState => {
    return {
        filters: state.tableFilter.filters,
        selectedCategory: state.tableFilter.category,
        selectedRoomId: state.tableFilter.roomId,
    };
};

const mapDispatchToProps = (dispatch: Dispatch<IStore>): IState => {
    return {
        onCategoryChange: (category: string) => dispatch(setCategory(category)),
        onRoomChange: (roomId: number) => dispatch(setRoom(roomId)),
    };
};

export default connect(
    mapStateToProps,
    mapDispatchToProps,
)(Options);

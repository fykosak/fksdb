import * as React from 'react';

import {connect} from 'react-redux';
import {
    setCategory,
    setRoom,
    setAutoSwitch,
} from '../../actions/table-filter';
import{setHardVisible} from '../../actions/options';
import {filters} from '../../helpers/filters';

interface IProps {
    onRoomChange?: Function;
    onCategoryChange?: Function;
    onAutoSwitchChange?: Function;
    onHardDisplayChange?: Function;
    isOrg?: boolean;
}

interface IState {
    isDisplayed: boolean;
}

class Options extends React.Component<IProps, IState> {
    constructor() {
        super();
        this.state = {
            isDisplayed: false,
        }
    }

    render() {
        const {
            onRoomChange,
            onCategoryChange,
            onAutoSwitchChange,
            onHardDisplayChange,
            isOrg,
        } = this.props;
        const {isDisplayed} = this.state;
        return (
            <div>
                <button
                    className={'btn btn-default '+(isDisplayed?'active':'')}
                    onClick={()=>this.setState({isDisplayed:!isDisplayed})}
                >
        <span
            className="glyphicon glyphicon-cog"
            type="button"/>
                    Nastavení
                </button >

                <div style={{display:isDisplayed?'block':'none'}}>
                    <div className="form-group">
                        <label className="sr-only">
                            <span>Místnost</span>
                        </label>
                        <select
                            className="form-control"
                            onChange={(event)=>onRoomChange(event.target.value)}>
                            {                                filters
                                .filter((filter) => filter.room != null)
                                .map((filter, index) => {
                                    return (<option key={index} value={filter.room}>{filter.name}</option>)
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
                            onChange={(event)=>onCategoryChange(event.target.value)}>

                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                        </select>
                    </div>

                    <div className="form-group">
                        <div className="checkbox">
                            <label>
                                <input type="checkbox" value="1"
                                       onChange={(event)=>onAutoSwitchChange(event.target.checked)}/>
                                <span>Automatické přepínání místností a kategorií</span>
                            </label>

                        </div>
                    </div>
                    <div className="form-group has-error">
                        <div className="checkbox">
                            <label>
                                <input type="checkbox" disabled={!isOrg} value="1"
                                       onChange={(event)=>onHardDisplayChange(event.target.checked)}/>
                                Neveřejné výsledkovky, <span className="text-danger">tuto funkci nezapínejte pokud jsou výsledkovky promítané!!!</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        );
    }
}

const mapStateToProps = (state, ownProps: IProps): IProps => {
    return {
        ...ownProps,
        isOrg: state.options.isOrg,
    };
};

const mapDispatchToProps = (dispatch, ownProps: IProps): IProps => {
    return {
        ...ownProps,
        onCategoryChange: (category) => dispatch(setCategory(category)),
        onRoomChange: (room) => dispatch(setRoom(room)),
        onAutoSwitchChange: (status) => dispatch(setAutoSwitch(status)),
        onHardDisplayChange: (status) => dispatch(setHardVisible(status)),
    };
};

export default connect(
    mapStateToProps,
    mapDispatchToProps,
)(Options);

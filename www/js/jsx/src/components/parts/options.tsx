import * as React from 'react';

import {connect} from 'react-redux';

interface IOptions {
    onCategoryChange: Function;
    onRoomChange: Function;
    onAutoSwitchChange: Function;
    onHardDisplayChange: Function;
    isOrg: boolean;
}

//import {filters} from '../../filters';
const filters = [];
class Options extends React.Component<IOptions, void> {

    render() {

        let {onCategoryChange, onRoomChange, onAutoSwitchChange, onHardDisplayChange, isOrg} = this.props;
        const isDisplayed = true;
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
                            onChange={()=>onRoomChange()}>
                            <option>--vyberte místnost--</option>
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
                            onChange={()=>onCategoryChange()}>
                            <option>--vyberte kategorii--</option>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                        </select>
                    </div>

                    <div className="form-group">
                        <div className="checkbox">
                            <label>
                                <input type="checkbox" value="1" onChange={()=>onAutoSwitchChange()}/>
                                <span>Automatické přepínání místností a kategorií</span>
                            </label>

                        </div>
                    </div>
                    <div className="form-group has-error">
                        <div className="checkbox">
                            <label>
                                <input type="checkbox" disabled={!isOrg} value="1"
                                       onChange={()=>onHardDisplayChange()}/>
                                Neveřejné výsledkovky, <span className="text-danger">tuto funkci nezapínejte pokud jsou výsledkovky promítané!!!</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        );
    }
}

const mapStateToProps = (state, ownProps) => {
    return {
        ...ownProps
    }
};

const mapDispatchToProps = (dispatch, ownProps) => {
    return {
        ...ownProps,
        onCategoryChange: dispatch(() => {
        }),
        onRoomChange: dispatch(() => {
        }),
        onAutoSwitchChange: dispatch(() => {
        }),
        onHardDisplayChange: dispatch(() => {
        }),
    }
};

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(Options);

import * as React from 'react';
import {connect} from 'react-redux';

import {filters} from '../../helpers/filters';

class Results extends React.Component<any, void> {

    public render() {

        // let {hardVisible}=this.state;
        // this.state.visible = (hardVisible);

        const filtersButtons = filters.map((filter, index) => {
            // className={(filter.room==this.state.displayRoom&&filter.category==this.state.displayCategory)?'active':''}

            /*  onClick={()=>{
             this.setState({displayCategory:filter.category,
             displayRoom:filter.room});
             }}*/
            return (
                <li key={index} role="presentation">
                    <a href="#">
                        {filter.name}
                    </a>
                </li>
            )
        });

        // style={{display:(this.state.visible)?'':'none'}}

        return (
                <ul className="nav nav-tabs">
                    {filtersButtons}
                </ul>
        );
    }
}

const mapStateToProps = (state, ownProps) => {
    return {
        ...ownProps,
        isReady: state.options.isReady,
    }
};

const mapDispatchToProps = (dispatch, ownProps) => {
    return {
        ...ownProps,
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(Results);
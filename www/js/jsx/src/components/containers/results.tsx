import * as React from 'react';
import {connect} from 'react-redux';

import Images from '../parts/images';
import ResultsTable from '../table/results-table';
import Timer from '../parts/timer';
import {basePath} from '../../helpers/base-path';
import {filters} from '../../helpers/filters';
import AutoFilter from '../parts/auto-filter';

interface IProps{
    isReady:boolean;
}

class Results extends React.Component<IProps, any> {

    public constructor() {
        super();
        this.state = {
            autoDisplayCategory: null,
            autoDisplayRoom: null,
            autoSwitch: false,
            hardVisible: false,
            displayCategory: null,
            displayRoom: null,

            visible: false,
            isOrg: false,
            configDisplay: false,
            msg: '',
            isRefreshing: false,
        };
    }

    public componentDidMount() {

        this.applyNextAutoFilter(0);
    }

    private applyNextAutoFilter(i) {
        $("html, body").scrollTop();

        let t = 15000;
        let {autoSwitch, autoDisplayCategory, autoDisplayRoom} = this.state;
        if (autoSwitch) {
            switch (i) {
                case 0: {
                    t = 30000;
                    this.setState({displayCategory: null, displayRoom: null});
                    break;
                }
                case 1: {
                    if (autoDisplayRoom) {
                        this.setState({displayCategory: autoDisplayCategory});
                    } else {
                        t = 0;
                    }
                    break;
                }
                case 2: {
                    if (autoDisplayCategory) {
                        this.setState({displayRoom: autoDisplayRoom});
                    } else {
                        t = 0;
                    }
                    break;
                }
            }
            if (t > 1000) {
                $("html, body").delay(t / 3).animate({scrollTop: $(document).height()}, t / 3);
            }
        }
        setTimeout(() => {
            i++;
            i = i % 3;
            this.applyNextAutoFilter(i);
        }, t);
    };


    public render() {
        let {hardVisible}=this.state;
        this.state.visible = (hardVisible);

        /* let filtersButtons = filters.map((filter, index) => {
         return (
         <li key={index} role="presentation"
         className={(filter.room==this.state.displayRoom&&filter.category==this.state.displayCategory)?'active':''}>
         <a onClick={()=>{
         this.setState({displayCategory:filter.category,
         displayRoom:filter.room});
         }}>
         {filter.name}
         </a>
         </li>
         )
         });*/

        const msg = [];
        if (hardVisible) {
            msg.push(<div key={msg.length} className="alert alert-warning">
                Výsledková listina je určená pouze pro organizátory!!!</div> );
        }
        if (!this.state.isOrg) {
            msg.push(
                <div key={msg.length} className="alert alert-info">
                    Na výsledkovou listinu se díváte jako "Public"</div>
            );
        }


        if (!this.props.isReady) {
            return (
                <div className="load" style={{textAlign:'center',}}>
                    <img src={basePath+'/images/gears.svg'} style={{width:'50%'}}/>
                </div>)
        }
//   <ul className="nav nav-tabs" style={{display:(this.state.visible)?'':'none'}}>
//        {filtersButtons}
        //   </ul>
        return (<div>
                {msg}


                <AutoFilter/>
                <Images/>
                <ResultsTable {...this.state} {...this.props}/>
                <Timer/>
            </div >
        );
    }
}

const mapStateToProps = (state, ownProps) => {
    return {
        ...ownProps,
        isReady: state.options.isReady,
    }
};

export default connect(mapStateToProps, null)(Results);
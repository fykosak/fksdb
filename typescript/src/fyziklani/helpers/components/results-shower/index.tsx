import * as React from 'react';
import { connect } from 'react-redux';
import { State as OptionsState } from '../../options/reducers';
import { State as TimerState } from '../../reducers/timer';
import Images from '../timer/Images';
import Timer from '../timer/Index';

interface State {
    visible?: boolean;
    hardVisible?: boolean;
}

interface Props {
    className?: string;
}

class ResultsShower extends React.Component<State & Props, {}> {

    public render() {
        const {visible, hardVisible} = this.props;
        // <Sponsors/>
        return (
            <>
                {(visible || hardVisible) ?
                    (<div>
                        <Timer mode={'small'}/>
                        {this.props.children}
                    </div>) :
                    (<div className={this.props.className}>
                        <div className={'logo row py-4'}>
                            <img className={'col-3 offset-1'} alt="" src="/images/fof/logo2.svg"/>
                        </div>
                        <Images/>
                        <Timer mode={'big'}/>
                    </div>)}
            </>
        );
    }
}

interface Store {
    timer: TimerState;
    options: OptionsState;
}

const mapStateToProps = (state: Store): State => {
    return {
        hardVisible: state.options.hardVisible,
        visible: state.timer.visible,
    };
};

export default connect(mapStateToProps, null)(ResultsShower);

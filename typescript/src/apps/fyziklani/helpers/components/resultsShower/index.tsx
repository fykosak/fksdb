import * as React from 'react';
import { connect } from 'react-redux';
import { State as OptionsState } from '../../options/reducers';
import { State as TimerState } from '../../reducers/timer';
import Timer from '../timer/';
import Images from '../timer/images';

interface StateProps {
    visible?: boolean;
    hardVisible?: boolean;
}

interface OwnProps {
    className?: string;
    children: React.ReactNode;
}

class ResultsShower extends React.Component<StateProps & OwnProps, {}> {

    public render() {
        const {visible, hardVisible} = this.props;
        return (
            <>
                {(visible || hardVisible) ?
                    (<div>
                        <Timer mode={'small'}/>
                        {this.props.children}
                    </div>) :
                    (<div className={this.props.className}>
                        <div className={'logo row'}>
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

const mapStateToProps = (state: Store): StateProps => {
    return {
        hardVisible: state.options.hardVisible,
        visible: state.timer.visible,
    };
};

export default connect(mapStateToProps, null)(ResultsShower);

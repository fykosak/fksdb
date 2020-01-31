import * as React from 'react';
import { connect } from 'react-redux';
import { State as OptionsState } from '../../hardVisible/reducers';
import Timer from '../../timer/components';
import Images from '../../timer/components/images';
import { State as TimerState } from '../../timer/reducers/timer';
import { FyziklaniResultsCoreStore } from '../reducers/coreStore';

interface StateProps {
    visible: boolean;
    hardVisible: boolean;
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

const mapStateToProps = (state: FyziklaniResultsCoreStore): StateProps => {
    return {
        hardVisible: state.options.hardVisible,
        visible: state.timer.visible,
    };
};

export default connect(mapStateToProps, null)(ResultsShower);

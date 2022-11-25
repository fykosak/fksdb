import Images
    from 'FKSDB/Components/Game/ResultsAndStatistics/ResultsPresentation/Components/Timer/Images';
import Timer from 'FKSDB/Components/Game/ResultsAndStatistics/ResultsPresentation/Components/Timer/Timer';
import * as React from 'react';
import { connect } from 'react-redux';
import { Store } from 'FKSDB/Components/Game/ResultsAndStatistics/reducers/store';

interface StateProps {
    visible: boolean;
    hardVisible: boolean;
}

interface OwnProps {
    className?: string;
    children: React.ReactNode;
    event: 'fof' | 'ctyrboj';
}

class Toggler extends React.Component<StateProps & OwnProps> {

    public render() {
        const {visible, hardVisible} = this.props;
        if (visible || hardVisible) {
            return <>
                <Timer mode="small"/>
                {this.props.children}
            </>;
        }
        return <div className={this.props.className}>
            <div className="logo row mt-3">
                <img className="col-6 offset-3" alt="" src={
                    this.props.event === 'fof'
                        ? '/images/fyziklani/logo_2022_white.svg'
                        : '/images/logo/vedecky_ctyrboj.png'
                }
                />
            </div>
            <Images/>
            <Timer mode="big"/>
        </div>;
    }
}

const mapStateToProps = (state: Store): StateProps => {
    return {
        hardVisible: state.presentation.hardVisible,
        visible: state.timer.visible,
    };
};

export default connect(mapStateToProps, null)(Toggler);

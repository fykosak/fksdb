import Images from 'FKSDB/Components/Game/ResultsAndStatistics/Presentation/Components/Timer/images';
import Timer from 'FKSDB/Components/Game/ResultsAndStatistics/Presentation/Components/Timer/timer';
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

function Toggler(props: StateProps & OwnProps) {
    const {visible, hardVisible} = props;
    if (visible || hardVisible) {
        return <>
            <Timer mode="small"/>
            {props.children}
        </>;
    }
    return <div className="h-100 d-flex flex-column justify-content-around align-items-center">
        <img className="w-50 logo" alt="" src={
            props.event === 'fof'
                ? '/images/fyziklani/fyziklani_2023_logo.png'
                : '/images/logo/vedecky_ctyrboj.png'
        }/>
        <Images/>
        <Timer mode="big"/>
        <img className="logo-sponsors" alt="" src={
            props.event === 'fof'
                ? '/images/fyziklani/fyziklani_2023_sponsors.svg' : ''
        }/>
    </div>;
}

const mapStateToProps = (state: Store): StateProps => {
    return {
        hardVisible: state.presentation.hardVisible,
        visible: state.timer.visible,
    };
};

export default connect(mapStateToProps, null)(Toggler);

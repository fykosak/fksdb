import Images
    from 'FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/ResultsPresentation/Components/Timer/Images';
import Timer from 'FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/ResultsPresentation/Components/Timer/Timer';
import * as React from 'react';
import { connect } from 'react-redux';
import { FyziklaniPresentationStore } from 'FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/ResultsPresentation/Reducers';

interface StateProps {
    visible: boolean;
    hardVisible: boolean;
}

interface OwnProps {
    className?: string;
    children: React.ReactNode;
}

class ResultsShower extends React.Component<StateProps & OwnProps> {

    public render() {
        const {visible, hardVisible} = this.props;
        return (
            <>
                {(visible || hardVisible) ?
                    (<>
                        <Timer mode="small"/>
                        {this.props.children}
                    </>) :
                    (<div className={this.props.className}>
                        <div className="logo row mt-3">
                            <img className="col-6 offset-3" alt="" src="/images/fyziklani/logo_2022_white.svg"/>
                        </div>
                        <Images/>
                        <Timer mode="big"/>
                    </div>)}
            </>
        );
    }
}

const mapStateToProps = (state: FyziklaniPresentationStore): StateProps => {
    return {
        hardVisible: state.presentation.hardVisible,
        visible: state.timer.visible,
    };
};

export default connect(mapStateToProps, null)(ResultsShower);

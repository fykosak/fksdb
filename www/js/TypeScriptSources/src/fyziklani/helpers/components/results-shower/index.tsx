import * as React from 'react';
import { connect } from 'react-redux';
import Timer from '../../../helpers/components/timer';
import Images from '../../../results/components/results/images';
import { IFyziklaniOptionsState } from '../../options/reducers';
import { IFyziklaniTimerState } from '../../reducers/timer';
import { lang } from '../../../../i18n/i18n';

interface IState {
    visible?: boolean;
    hardVisible?: boolean;

}

interface IProps {
    className?: string;
}

class ResultsShower extends React.Component<IState & IProps, {}> {

    public render() {
        const {visible, hardVisible} = this.props;

        const msg = [];
        if (hardVisible) {
            msg.push(<div key={msg.length} className="alert alert-warning">
                {lang.getText('Výsledková listina je určená pouze pro organizátory!!!')}
            </div>);
        }

        return (
            <>
                {msg}
                {(visible || hardVisible) ?
                    (<div>
                        <Timer/>
                        {this.props.children}
                    </div>) :
                    (<div className={this.props.className}>
                        <Timer/>
                        <Images/>
                    </div>)}
            </>
        );
    }
}

interface IStore {
    timer: IFyziklaniTimerState;
    options: IFyziklaniOptionsState;
}

const mapStateToProps = (state: IStore): IState => {
    return {
        hardVisible: state.options.hardVisible,
        visible: state.timer.visible,
    };
};

export default connect(mapStateToProps, null)(ResultsShower);

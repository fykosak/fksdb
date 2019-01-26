import * as React from 'react';
import { connect } from 'react-redux';
import { lang } from '../../../../i18n/i18n';
import Timer from '../../../helpers/components/timer';
import Images from '../../../results/components/results/images';
import { State as OptionsState } from '../../options/reducers';
import { State as TimerState } from '../../reducers/timer';

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

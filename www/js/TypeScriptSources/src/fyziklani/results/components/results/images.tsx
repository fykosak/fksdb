import * as React from 'react';
import { connect } from 'react-redux';
import { getCurrentDelta } from '../../../../results/helpers/timer';
import { IFyziklaniResultsStore } from '../../reducers';

interface IState {
    toStart?: number;
    toEnd?: number;
    inserted?: Date;
    visible?: boolean;
}

interface IProps {
    basePath: string;
}

class Images extends React.Component<IState & IProps, {}> {
    private timerId = null;

    public componentDidMount() {
        this.timerId = setInterval(() => this.forceUpdate(), 1000);
    }

    public componentWillUnmount() {
        clearInterval(this.timerId);
    }

    public render() {
        const {inserted, toStart, toEnd, basePath} = this.props;
        const {currentToStart, currentToEnd} = getCurrentDelta({toStart, toEnd}, inserted);

        if (currentToStart === 0 || currentToEnd === 0) {
            return (<div/>);
        }
        let imgSRC = basePath + 'images/fyziklani/';
        if (currentToStart > 300 * 1000) {
            imgSRC += 'nezacalo.svg';
        } else if (currentToStart > 0) {
            imgSRC += 'brzo.svg';
        } else if (currentToStart > -120 * 1000) {
            imgSRC += 'start.svg';
        } else if (currentToEnd > 0) {
            imgSRC += 'fyziklani.svg';

        } else if (currentToEnd > -240 * 1000) {
            imgSRC += 'skoncilo.svg';
        } else {
            imgSRC += 'ceka.svg';
        }
        return (
            <div id='imageWP' data-basepath={basePath}>
                <img src={imgSRC} alt="" style={{width: '80%'}}/>
            </div>
        );
    }
}

const mapStateToProps = (state: IFyziklaniResultsStore): IState => {
     return {
         inserted: state.timer.inserted,
         toEnd: state.timer.toEnd,
         toStart: state.timer.toStart,
         visible: state.timer.visible,
     };
};

export default connect(mapStateToProps, (): IState => {
    return {};
})(Images);

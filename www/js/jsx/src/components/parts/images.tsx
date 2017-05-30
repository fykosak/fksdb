import * as React from 'react';
import {connect} from 'react-redux';
import {basePath} from '../../helpers/base-path';
import {getCurrentDelta} from '../../helpers/timer';

interface IProps {
    toStart?: number;
    toEnd?: number;
    inserted?: Date;
    visible?: boolean;
}

class Images extends React.Component<IProps,void> {
    componentDidMount() {
        setInterval(() => this.forceUpdate(), 1000);
    }

    public render() {
        const {inserted, toStart, toEnd}=this.props;
        const {currentToStart, currentToEnd} = getCurrentDelta({toStart, toEnd}, inserted);

        if (currentToStart == 0 || currentToEnd == 0) {
            return (<div/>);
        }
        let imgSRC = basePath + '/images/fyziklani/';
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
                <img src={imgSRC} alt="" style={{width:'80%'}}/>
            </div>
        )
    }
}

const mapStateToProps = (state, ownProps) => {
    return {
        ...ownProps,
        ...state.timer,
    }
};

export default connect(mapStateToProps, null)(Images);

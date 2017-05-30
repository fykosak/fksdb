import * as React from 'react';
import {connect} from 'react-redux';
import {basePath} from '../../helpers/base-path';

interface IProps {
    toStart?: number;
    toEnd?: number;
    visible?: boolean;
}

class Images extends React.Component<IProps,void> {

    public render() {
        const {toStart, toEnd}=this.props;
        if (toStart == 0 || toEnd == 0) {
            return (<div/>);
        }
        let imgSRC = basePath + '/images/fyziklani/';
        if (toStart > 300) {
            imgSRC += 'nezacalo.svg';
        } else if (toStart > 0) {
            imgSRC += 'brzo.svg';
        } else if (toStart > -120) {
            imgSRC += 'start.svg';
        } else if (toEnd > 0) {
            imgSRC += 'fyziklani.svg';

        } else if (toEnd > -240) {
            imgSRC += 'skoncilo.svg';
        } else {
            imgSRC += 'ceka.svg';
        }
        return (
            <div id='imageWP' data-basepath={basePath}>
                <img src={imgSRC} alt=""/>
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

import * as React from 'react';
import {
    Field,
} from 'redux-form';
import Price from './price';

import { connect } from 'react-redux';
import { IScheduleItem } from '../../middleware/iterfaces';
import { IStore } from '../../reducers';
import Item from './item';

interface IProps {
    type: string;
    index: number;
}

interface IState {
    scheduleDef?: IScheduleItem[];
}

class Schedule extends React.Component<IProps & IState, {}> {

    public render() {
        const {type, index} = this.props;
        return <>
            <p>Doprovodný program o ktorý mám zaujem.</p>
            {this.props.scheduleDef.map((value) => {
                return <Field
                    name={value.id}
                    component={Item}
                    date={value.date}
                    description={value.description}
                    scheduleName={value.scheduleName}
                    price={value.price}
                    id={value.id}
                    time={value.time}
                />;
            })}
            <Price type={type} index={index}/>
        </>;

    }
}

const mapDispatchToProps = (): IState => {
    return {};
};

const mapStateToProps = (state: IStore): IState => {
    return {
        scheduleDef: state.definitions.schedule,
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(Schedule);

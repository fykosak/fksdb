import * as React from 'react';
import { connect } from 'react-redux';
import { Field } from 'redux-form';
import Lang from '../../../../../lang/components/lang';
import { IScheduleItem } from '../../../../middleware/iterfaces';
import { IPersonSelector } from '../../../../middleware/price';
import { IStore } from '../../../../reducers';
import Item from './item';
import Price from './price';

interface IState {
    scheduleDef?: IScheduleItem[];
}

class Schedule extends React.Component<IPersonSelector & IState, {}> {

    public render() {
        const {type, index} = this.props;
        return <>
            <p><Lang text={'Doprovodný program o ktorý mám zaujem.'}/></p>
            {this.props.scheduleDef.map((value, i) => {
                return <Field
                    key={i}
                    name={value.id.toString()}
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

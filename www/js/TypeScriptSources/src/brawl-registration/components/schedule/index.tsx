import * as React from 'react';
import {
    Field,
    FormSection,
} from 'redux-form';

import { getFieldName } from '../containers/persons';
import Item from './item';

interface IProps {
    type: string;
    index: number;
}

interface IState {

}

const scheduleDef = [
    {
        date: '2017-05-02',
        description: '<a href="http://matfyz.cz">viac info</a> jeden den s fyzikou',
        id: 1,
        name: 'JDF',
        time: {
            begin: '12:00',
            end: '15:00',
        },
    },
    {
        date: '2017-04-02',
        description: 'DSEF',
        id: 2,
        name: 'DSEF',
        time: {
            begin: '12:00',
            end: '15:00',
        },
    },
    {
        date: '2017-06-02',
        description: 'Afterparty',
        id: 3,
        name: 'Afterparty',
        time: {
            begin: '12:00',
            end: '15:00',
        },
    },
];

export default class Schedule extends React.Component<IProps & IState, {}> {

    public render() {
        return <div>
            <h3>Schedule</h3>
            <p>Doprovodný program o ktorý mám zaujem.</p>
            {scheduleDef.map((value) => {
                return <Field
                    name={'schedule' + value.id}
                    component={Item}
                    date={value.date}
                    description={value.description}
                    scheduleName={value.name}
                    id={value.id}
                    time={value.time}
                />;
            })}
        </div>;

    }
}

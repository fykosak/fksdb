import * as React from 'react';
import { FormSection } from 'redux-form';
import Tab from '../helpers/tabs/tab';
import PersonProvider from '../person-provider';
import NameDisplay from '../displays/name-display';
import ParticipantForm from './participant';
import { getFieldName } from './persons';
import TeacherForm from './teacher';

interface IProps {
    type: string;
    index: number;
}

export default class TabItem extends React.Component<IProps, {}> {
    public render() {
        const {index, type} = this.props;
        let form = null;
        switch (type) {
            default:
            case 'participant':
                form = <ParticipantForm index={index} type={type}/>;
                break;
            case 'teacher':
                form = <TeacherForm index={index} type={type}/>;

        }
        return <FormSection key={index} name={getFieldName(type, index)}>
            <Tab active={this.props.index === 0} name={('member' + this.props.index)}>
                <PersonProvider index={index} type={type}>
                    {form}
                </PersonProvider>
            </Tab>
        </FormSection>;
    }
}

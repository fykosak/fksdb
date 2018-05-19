import * as React from 'react';
import { FormSection } from 'redux-form';
import Tab from '../helpers/tabs/tab';
import PersonProvider from '../../../person-provider/components/provider';
import ParticipantForm from './participant';
import { getFieldName } from './persons';
import TeacherForm from './teacher';

interface IProps {
    type: string;
    index: number;
    active: boolean;
}

export default class TabItem extends React.Component<IProps, {}> {
    public render() {
        const {index, type, active} = this.props;
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
            <Tab active={active} name={(type + index)}>
                <PersonProvider accessKey={getFieldName(type, index)}>
                    {form}
                </PersonProvider>
            </Tab>
        </FormSection>;
    }
}

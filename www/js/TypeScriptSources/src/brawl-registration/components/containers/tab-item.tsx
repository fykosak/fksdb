import * as React from 'react';
import { connect } from 'react-redux';
import {
    Field,
    FormSection,
} from 'redux-form';
import PersonProvider from '../../../person-provider/components/provider';
import { required } from '../../../person-provider/validation';
import Tab from '../helpers/tabs/tab';
import HiddenField from '../inputs/hidden';
import ParticipantForm from './participant';
import { getFieldName } from './persons';
import TeacherForm from './teacher';

interface IProps {
    type: string;
    index: number;
    active: boolean;
    required?: boolean;
}

interface IState {
    providerOpt?: {
        personId?: { hasValue: boolean; value: string };
    };
}

class TabItem extends React.Component<IProps & IState, {}> {
    public render() {
        const {index, type, active, providerOpt} = this.props;
        let personId = null;
        if (providerOpt) {
            personId = providerOpt.personId;
        }
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
                <Field
                    name={'personId'}
                    validate={this.props.required ? [required] : []}
                    component={HiddenField}
                    providerOptions={personId}
                />
                <PersonProvider accessKey={getFieldName(type, index)}>
                    {form}
                </PersonProvider>
            </Tab>
        </FormSection>;
    }
}

const mapDispatchToProps = (): IState => {
    return {};
};

const mapStateToProps = (state, ownProps: IProps): IState => {
    const accessKey = getFieldName(ownProps.type, ownProps.index);
    if (state.provider.hasOwnProperty(accessKey)) {
        return {
            providerOpt: {
                personId: state.provider[accessKey].personId,
            },
        };
    }
    return {};
};

export default connect(mapStateToProps, mapDispatchToProps)(TabItem);

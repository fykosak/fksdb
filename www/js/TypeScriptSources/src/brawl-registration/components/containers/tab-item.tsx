import * as React from 'react';
import { connect } from 'react-redux';
import NameDisplay from '../displays/name';
import {
    Field,
    FormSection,
} from 'redux-form';
import PersonProvider from '../../../person-provider/components/provider';
import { required } from '../../../person-provider/validation';
import { getFieldName } from '../../middleware/person';
import ParticipantForm from '../form/groups/participant';
import TeacherForm from '../form/groups/teacher';
import Tab from '../helpers/tabs/tab';
import HiddenField from '../inputs/hidden';

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
        const accessKey = getFieldName(type, index);
        const personId = null;

        let form = null;
        switch (type) {
            default:
            case 'participant':
                form = <ParticipantForm accessKey={accessKey} index={index} type={type}/>;
                break;
            case 'teacher':
                form = <TeacherForm accessKey={accessKey} index={index} type={type}/>;

        }

        interface IInputDef {
            required: boolean;
            secure: boolean;

        };
        const inputsDef: { [key: string]: { [key: string]: IInputDef } } = {
            person: {
                family_name: {
                    required: true,
                    secure: false,
                },
                other_name: {
                    required: true,
                    secure: false,
                },
            },
            person_history: {
                school_id: {
                    required: true,
                    secure: false,
                },
            },
        };

        return <FormSection key={index} name={getFieldName(type, index)}>
            <Tab active={active} name={(type + index)}>
                <h2><NameDisplay index={index} type={type}/></h2>
                <Field
                    name={'personId'}
                    validate={this.props.required ? [required] : []}
                    component={HiddenField}
                    providerOptions={personId}
                />
                <PersonProvider accessKey={getFieldName(type, index)} inputs={}>
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

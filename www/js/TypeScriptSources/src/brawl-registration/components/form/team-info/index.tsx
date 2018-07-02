import * as React from 'react';
import { connect } from 'react-redux';
import { Field } from 'redux-form';
import Lang from '../../../../lang/components/lang';
import BaseInput from '../../../../person-provider/components/inputs/base-input';
import Select from '../../../../person-provider/components/inputs/select';
import { required } from '../../../../person-provider/validation';

class TeamInfo extends React.Component<{}, {}> {

    public render() {

        return (
            <div>
                <Field
                    validate={[required]}
                    name={'team_name'}
                    inputType={'text'}
                    readonly={false}
                    component={BaseInput}
                    JSXLabel={<Lang text={'Team name'}/>}
                />
                <Field
                    validate={[required]}
                    name={'team_lang'}
                    readonly={false}
                    component={Select}
                    children={<>
                        <option value={'cs'}>Česky</option>
                        <option value={'en'}>Anglicky</option>
                    </>}
                    JSXLabel={<Lang text={'Team language'}/>}
                    JSXDescription={<Lang text={'Jazyk ve ktreremc chete soutežit'}/>}
                />

            </div>
        );
    }
}

export const FORM_NAME = 'brawlRegistrationForm';

const mapDispatchToProps = (): {} => {
    return {};
};

const mapStateToProps = (): {} => {
    return {};

};

export default connect(mapStateToProps, mapDispatchToProps)(TeamInfo);

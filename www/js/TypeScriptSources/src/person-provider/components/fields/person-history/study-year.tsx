import * as React from 'react';
import { connect } from 'react-redux';
import { Field } from 'redux-form';
import Select, { ISelectInputProps } from '../../../../brawl-registration/components/inputs/select';
import { IPersonStringSelectror } from '../../../../brawl-registration/middleware/price';
import { IStore } from '../../../../brawl-registration/reducers';
import Lang from '../../../../lang/components/lang';
import { required } from '../../../validation';
import InputProvider from '../../input-provider';

class Input extends InputProvider<ISelectInputProps> {
}

interface IState {
    studyYearsDef?: any[];
}

class StudyYear extends React.Component<IPersonStringSelectror & IState, {}> {

    public render() {
        const {accessKey, studyYearsDef} = this.props;
        const optGroups = [];
        for (const name in studyYearsDef) {
            if (studyYearsDef.hasOwnProperty(name)) {
                const opts = [];
                for (const value in studyYearsDef[name]) {
                    if (studyYearsDef[name].hasOwnProperty(value)) {
                        opts.push(<option key={value} value={value}>{studyYearsDef[name][value]}</option>);
                    }
                }
                optGroups.push(<optgroup key={name} label={name}>
                    {opts}
                </optgroup>);
            }
        }
        return <Field
            accessKey={accessKey}
            JSXLabel={<Lang text={'Study year'}/>}
            secure={true}
            component={Input}
            providerInput={Select}
            children={optGroups}
            name={'personHistory.studyYear'}
            validate={[required]}
        />;
    }
}

const mapDispatchToProps = (): IState => {
    return {};
};

const mapStateToProps = (state: IStore): IState => {
    return {
        studyYearsDef: state.definitions.studyYears,
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(StudyYear);

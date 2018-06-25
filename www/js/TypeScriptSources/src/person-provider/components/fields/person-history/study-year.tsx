import * as React from 'react';
import { connect } from 'react-redux';
import { Field } from 'redux-form';
import Select, { ISelectInputProps } from '../../../../brawl-registration/components/inputs/select';
import { IPersonSelector } from '../../../../brawl-registration/middleware/price';
import { IStore } from '../../../../brawl-registration/reducers';
import Lang from '../../../../lang/components/lang';
import { required as requiredTest } from '../../../validation';
import InputProvider from '../../input-provider';
import { IInputDefinition } from '../interfaces';

class Input extends InputProvider<ISelectInputProps> {
}

interface IState {
    studyYearsDef?: any[];
}

interface IProps {
    def: IInputDefinition;
    name: string;
    personSelector: IPersonSelector;
}

class StudyYear extends React.Component<IProps & IState, {}> {

    public render() {
        const {personSelector: {accessKey}, def: {required, readonly, secure}, name, def} = this.props;
        const {studyYearsDef} = this.props;
        const optGroups = [];
        for (const group in studyYearsDef) {
            if (studyYearsDef.hasOwnProperty(group)) {
                const opts = [];
                for (const value in studyYearsDef[group]) {
                    if (studyYearsDef[group].hasOwnProperty(value)) {
                        opts.push(<option key={value} value={value}>{studyYearsDef[group][value]}</option>);
                    }
                }
                optGroups.push(<optgroup key={group} label={group}>
                    {opts}
                </optgroup>);
            }
        }
        return <Field
            accessKey={accessKey}
            inputDef={def}
            JSXLabel={<Lang text={'Study year'}/>}
            secure={secure}
            component={Input}
            providerInput={Select}
            children={optGroups}
            readonly={readonly}
            name={name}
            validate={required ? [requiredTest] : []}
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

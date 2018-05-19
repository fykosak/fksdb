import * as React from 'react';
import { connect } from 'react-redux';
import { IStore } from '../../reducers';
import { WrappedFieldProps } from 'redux-form';
import { IProviderValue } from '../../../person-provider/reducers/provider';

interface IState {
    studyYearsDef?: any[];
}

interface IProps {
    type: string;
    readOnly: boolean;
    providerOptions: IProviderValue;
}

class StudyYear extends React.Component<WrappedFieldProps & IProps & IState, {}> {

    public componentDidMount() {
        if (this.props.providerOptions.hasValue) {
            this.props.input.onChange(this.props.providerOptions.value);
        }
    }

    public render() {
        const {
            input,
            studyYearsDef,
        } = this.props;
        const optGroups = [];
        for (const name in studyYearsDef) {
            if (studyYearsDef.hasOwnProperty(name)) {
                const opts = [];
                for (const value in studyYearsDef[name]) {
                    if (studyYearsDef[name].hasOwnProperty(value)) {
                        opts.push(<option value={value}>{studyYearsDef[name][value]}</option>)
                    }
                }
                optGroups.push(<optgroup label={name}>
                    {opts}
                </optgroup>);
            }

        }
        return <select className="form-control" {...input}>
            {optGroups}
        </select>;
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

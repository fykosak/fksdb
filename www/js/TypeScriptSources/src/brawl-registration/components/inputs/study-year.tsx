import * as React from 'react';
import { connect } from 'react-redux';
import { WrappedFieldProps } from 'redux-form';
import { IInputProps } from '../../../person-provider/components/input-provider';
import { IStore } from '../../reducers';
import ErrorDisplay from './error-display';

interface IState {
    studyYearsDef?: any[];
}

class StudyYear extends React.Component<WrappedFieldProps & IInputProps & IState, {}> {

    public render() {
        const {
            input,
            studyYearsDef,
            JSXLabel,
            JSXDescription,
            meta,
        } = this.props;
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

        return <div className="form-group">
            <label>{JSXLabel}</label>
            {JSXDescription && (<small className="form-text text-muted">{JSXDescription}</small>)}
            <select className="form-control" {...input}>
                {optGroups}
            </select>
            <ErrorDisplay input={input} meta={meta}/>
        </div>;
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

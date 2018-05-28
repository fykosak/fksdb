import * as React from 'react';
import { WrappedFieldProps } from 'redux-form';
import BaseInput from './base-input';
import ErrorDisplay from './error-display';
import Lang from '../../../lang/components/lang';

interface IProps {

}

export default class TeamName extends React.Component<WrappedFieldProps & IProps, {}> {
    public render() {
        const {input, meta, meta: {valid, touched}} = this.props;
        return <div className={'form-group' + ((valid && touched) ? ' was-validated' : '')}>

            <label><Lang text={'Team name'}/></label>

            <BaseInput
                input={input}
                meta={meta}
                placeholder={'team name'}
                type={'text'}
                readOnly={false}
            />
            <ErrorDisplay
                input={input}
                meta={meta}
            />
        </div>;
    }
}

import * as React from 'react';
import { WrappedFieldProps } from 'redux-form';
import Lang from '../../../../../lang/components/lang';
import BaseInput from '../../../inputs/base-input';
import ErrorDisplay from '../../../inputs/error-display';

interface IProps {

}

export default class Container extends React.Component<WrappedFieldProps & IProps, {}> {
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

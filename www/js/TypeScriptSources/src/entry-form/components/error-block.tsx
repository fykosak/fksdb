import * as React from 'react';

export default class CodeInputErrorsBlock extends React.Component<any, {}> {

    public render() {
        const { meta: { valid, error } } = this.props;
        return (
            <span className={'input-group ' + (valid ? 'text-success' : 'invalid-feedback')}>
                {error ? error.msg : 'OK'}
            </span>
        );
    }
}

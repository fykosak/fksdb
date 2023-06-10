import { SubmitFormRequest } from 'FKSDB/Components/Game/Submits/Form/actions';
import { DataResponse } from 'vendor/fykosak/nette-frontend-component/src/Responses/response';
import * as React from 'react';
import { SubmitHandler } from 'redux-form';
import { translator } from '@translator/translator';

interface OwnProps {
    valid: boolean;
    submitting: boolean;
    handleSubmit: SubmitHandler<{ code: string }>;

    onSubmit(values: SubmitFormRequest): Promise<DataResponse<SubmitFormRequest>>;
}

export default class AutoButton extends React.Component<OwnProps, never> {

    public componentDidUpdate() {
        const {valid, submitting, handleSubmit, onSubmit} = this.props;
        if (valid && !submitting) {
            handleSubmit((values: { code: string }) =>
                onSubmit({
                    ...values,
                    points: null,
                }))();
        }
    }

    public render() {
        const {valid, submitting, handleSubmit, onSubmit} = this.props;
        return (
            <div className="d-flex justify-content-around">
                <button
                    className={'btn btn-lg ' + (valid ? 'btn-outline-success' : 'btn-outline-secondary')}
                    type="button"
                    disabled={!valid || submitting}
                    onClick={handleSubmit((values: { code: string }) =>
                        onSubmit({
                            ...values,
                            points: null,
                        }))}
                >{submitting ? (
                    <i className="fas fa-spinner fa-spin" aria-hidden="true"/>) : translator.getText('Submit')}</button>
            </div>
        );
    }
}

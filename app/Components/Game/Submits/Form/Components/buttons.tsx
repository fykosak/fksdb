import { SubmitFormRequest } from 'FKSDB/Components/Game/Submits/Form/actions';
import { DataResponse } from 'vendor/fykosak/nette-frontend-component/src/Responses/response';
import * as React from 'react';
import { SubmitHandler } from 'redux-form';

interface OwnProps {
    valid: boolean;
    submitting: boolean;
    availablePoints: number[];
    handleSubmit: SubmitHandler<{ code: string }>;

    onSubmit?(values: SubmitFormRequest): Promise<DataResponse<SubmitFormRequest>>;
    refCallback(index: number, node: HTMLElement): void;
}

export default function Buttons({valid, submitting, handleSubmit, onSubmit, refCallback, availablePoints}: OwnProps) {
    const buttons = availablePoints.map((value, index) => {
        return <button
            className={'btn btn-lg ' + (valid ? 'btn-outline-success' : 'btn-outline-secondary')}
            id={"pointsButton-" + value}
            key={index}
            type="button"
            disabled={!valid || submitting}
            ref={node => refCallback(value, node)}
            onClick={handleSubmit((values: { code: string }) =>
                onSubmit({
                    ...values,
                    points: value,
                }))}
        >{submitting ? (
            <i className="fas fa-spinner fa-spin" aria-hidden="true"/>) : (value + '. bodu')}
        </button>;
    });
    return <div className="d-flex justify-content-around">
        {buttons}
    </div>;
}

import { SubmitFormRequest } from 'FKSDB/Components/Controls/Fyziklani/Submit/actions';
import { Response2 } from 'FKSDB/Models/FrontEnd/Fetch/interfaces';
import * as React from 'react';
import { SubmitHandler } from 'redux-form';

interface OwnProps {
    valid: boolean;
    submitting: boolean;
    availablePoints: number[];
    handleSubmit: SubmitHandler<{ code: string }, any>;

    onSubmit?(values: SubmitFormRequest): Promise<Response2<void>>;
}

export default class SubmitButtons extends React.Component<OwnProps, {}> {

    public render() {
        const {valid, submitting, handleSubmit, onSubmit, availablePoints} = this.props;

        const buttons = availablePoints.map((value, index) => {
            return (
                <button
                    className={'btn btn-lg ' + (valid ? 'btn-success' : 'btn-outline-secondary')}
                    key={index}
                    type="button"
                    disabled={!valid || submitting}
                    onClick={handleSubmit((values: { code: string }) =>
                        onSubmit({
                            ...values,
                            points: value,
                        }))}
                >{submitting ? (<i className="fa fa-spinner fa-spin" aria-hidden="true"/>) : (value + '. bodu')}</button>
            );
        });
        return (
            <div className="d-flex justify-content-around">
                {buttons}
            </div>
        );
    }
}

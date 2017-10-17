import * as React from 'react';

interface IProps {
    valid: boolean;
    submitting: boolean;
    handleSubmit: (values: any) => any;
    onSubmit: any;
}

export default class TaskInput extends React.Component<IProps, {}> {

    public render() {
        const { valid, submitting, handleSubmit } = this.props;

        const buttons = [5, 3, 2, 1].map((value, index) => {
            return (
                <button
                    className={valid ? 'btn btn-success' : 'btn btn-outline-secondary'}
                    key={index}
                    disabled={!valid || submitting}
                    onClick={handleSubmit((values) =>
                        this.props.onSubmit({
                            ...values,
                            points: value,
                        }))}
                >{submitting ? (<i className="fa fa-spinner" aria-hidden="true"/>) : (value + '. bodu')}</button>
            );
        });
        return (
            <div className="card card-outline-info">
                <div className="card-header card-info">Počet bodov</div>
                <div className="card-block">
                    <div className="btn-group">
                        {buttons}
                    </div>
                </div>
            </div>
        );
    }
}

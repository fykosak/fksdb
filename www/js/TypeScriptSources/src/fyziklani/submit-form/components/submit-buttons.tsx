import * as React from 'react';

interface IProps {
    valid: boolean;
    submitting: boolean;
    availablePoints: number[];

    handleSubmit(values: any): any;

    onSubmit(args: any): any;
}

export default class TaskInput extends React.Component<IProps, {}> {

    public render() {
        const {valid, submitting, handleSubmit, onSubmit, availablePoints} = this.props;

        const buttons = availablePoints.map((value, index) => {
            return (
                <button
                    className={valid ? 'btn btn-success' : 'btn btn-outline-secondary'}
                    key={index}
                    type="button"
                    disabled={!valid || submitting}
                    onClick={handleSubmit((values) =>
                        onSubmit({
                            ...values,
                            points: value,
                        }))}
                >{submitting ? (<i className="fa fa-spinner" aria-hidden="true"/>) : (value + '. bodu')}</button>
            );
        });
        return (
            <div className="btn-group">
                {buttons}
            </div>
        );
    }
}

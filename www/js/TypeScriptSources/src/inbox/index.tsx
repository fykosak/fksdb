import * as React from 'react';
import * as ReactDOM from 'react-dom';
import DownloadLink from './download-link';

class Inbox extends React.Component<{
    element: HTMLInputElement;
}, {}> {

    public render() {
        const {element} = this.props;
        const taskData: { [key: number]: ISubmit } = JSON.parse(element.value);
        const inputs = [];
        for (const index in taskData) {
            if (taskData.hasOwnProperty(index)) {
                const data = taskData[index];
                if (data && data.source === 'upload') {
                    inputs.push(<DownloadLink submitId={data.submit_id} label={data.task.label} key={index}/>);
                } else {
                    data.source = 'post';
                    inputs.push(<DateTimeInput key={index} taskData={data} onChange={(value) => {
                        element.value = JSON.stringify({
                            ...taskData,
                            [index]: {
                                ...data,
                                submitted_on: value,
                            },
                        });
                        this.forceUpdate();

                    }
                    }/>);
                }
            }
        }
        return <div className="d-inline-flex">
            {inputs}
        </div>;

    }

}

interface IProps {
    taskData: ISubmit;
    onChange: (data: string) => void;
}

class DateTimeInput extends React.Component<IProps, {}> {
    public render() {
        const {taskData, onChange} = this.props;

        const handleChange = (event) => {
            const {value} = event.target;
            onChange(value ? value : null);
        };

        return <div className="flex-fill">
            <input
                type="date"
                value={taskData.submitted_on ? taskData.submitted_on : ''}
                className="form-control form-control-sm"
                placeholder={'Úloha ' + taskData.task.label}
                onChange={(event) => {
                    event.preventDefault();
                    handleChange(event);
                }
                }/>
        </div>;
    }
}

interface ITask {
    label: string;
    disabled: boolean;
}

interface ISubmit {
    calc_points: number;
    ct_id: number;
    note: string;
    raw_points: number;
    source: 'post' | 'upload';
    submit_id: number;
    submitted_on: string;
    task: ITask;
    task_id: number;
}

document.querySelectorAll('input.inbox').forEach((el: HTMLInputElement) => {
    ReactDOM.render(<Inbox element={el}/>, el.parentElement);
});

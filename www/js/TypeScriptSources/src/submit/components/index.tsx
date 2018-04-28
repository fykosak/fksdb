import * as React from "react";
import * as ReactDOM from "react-dom";

import Card from '../../shared/components/card';
import UploadedFile from './uploaded-file';

const el = document.getElementById('ajax-submit-form');

interface ITask {
    taskId: number;
    name: string;
    deadline: string;
}

interface ISubmit {
    submitId: number;
    name: string;
    href: string;
}

interface IData {
    [key: number]: { task: ITask; submit?: ISubmit };
}

interface IProps {
    data: IData;
}

class UploadForm extends React.Component<{ task: ITask; submit?: ISubmit }, {}> {
    public render() {
        const onUploadFile = (event) => {
            event.preventDefault();
            const data2 = event.dataTransfer.files;

            // if (form && form instanceof HTMLFormElement) {
            const formData = new FormData();
            for (const i in data2) {
                if (data2.hasOwnProperty(i)) {
                    formData.append('task' + this.props.task.taskId, data2[i]);
                }
            }
            uploadFile(formData);
            // }
        };
        return <div className="drop-input"
                    onDrop={onUploadFile}
                    onDragOver={(event) => {
                        event.preventDefault();
                    }
                    }>
            <div className="text-center">
                <span className="display-1 d-block"><i className="fa fa-download"/></span>
                <span className="d-block"> <strong>Choose a file</strong>
                        <span className="box__dragndrop"> or drag it here</span>.</span>
            </div>
        </div>;
    }

}

class App extends React.Component<IProps, {}> {

    public render() {

        console.log(this.props.data);
        const form = document.getElementById('frm-uploadForm');
        if (form && form instanceof HTMLFormElement) {
            const boxes = [];
            for (const taskId in this.props.data) {
                if (this.props.data.hasOwnProperty(taskId)) {
                    const data = this.props.data[taskId];
                    boxes.push(<div className="col-6 mb-3" key={taskId}>
                        <Card headline={data.task.name + ' - ' + data.task.deadline} level={'info'}>
                            {data.submit ? (
                                    <UploadedFile name={data.submit.name} href={data.submit.href} submitId={data.submit.submitId}/>) :
                                <UploadForm {...data}/>}

                        </Card>
                    </div>);
                }
            }
            return <div className="row">{boxes}</div>;

        }
        return null;

    }
}

const uploadFile = (formData: FormData) => {
    return new Promise((resolve, reject) => {
        $.ajax({
            cache: false,
            complete: () => {
                //    $form.removeClass('is-uploading');
            },
            contentType: false,
            data: formData,
            dataType: 'json',
            error: (e) => {
                reject(e);
                // Log the error, show an alert, whatever works for you
            },
            processData: false,
            success: (data) => {
                resolve(data);
                console.log(data);
                //  $form.addClass( data.success == true ? 'is-success' : 'is-error' );
                // if (!data.success) $errorMsg.text(data.error);
            },
            type: 'POST',
            url: '#',
        });
    });
};

if (el) {
    const data = JSON.parse(el.getAttribute('data-upload-data'));
    ReactDOM.render(<App data={data}/>, el);
}

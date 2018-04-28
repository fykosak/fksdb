import * as React from "react";
import * as ReactDOM from "react-dom";
import { netteFetch } from '../../shared/helpers/fetch';

const el = document.getElementById('ajax-submit-form');

const netteJQuery: any = $;

class App extends React.Component<any, {}> {

    public render() {
        const form = document.getElementById('frm-uploadForm');
        if (form && form instanceof HTMLFormElement) {
            const boxes = [];
            form.querySelectorAll('input[type="file"]').forEach((input: HTMLInputElement, index: number) => {
                const name = input.getAttribute('name');
                boxes.push(<div key={index} style={{backgroundColor: '#ccc', height: "200px", width: "100%"}} onDrop={(event) => {
                    event.preventDefault();
                    const data2 = event.dataTransfer.files;
                    console.log(data2);
                    console.log(event);

                    console.log(form);
                    if (form && form instanceof HTMLFormElement) {
                        const formData = new FormData(form);
                        for (const i in data2) {
                            if (data2.hasOwnProperty(i)) {
                                formData.append(name, data2[i]);
                            }
                        }
                        uploadFile(formData);
                    }

                }} onDragOver={(event) => {
                    event.preventDefault();
                }
                }>
                    <label htmlFor="file">
                        <strong>Choose a file</strong>
                        <span className="box__dragndrop"> or drag it here</span>.
                    </label>
                </div>);

            });
            return <>{boxes}</>;

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
    ReactDOM.render(<App/>, el);
}

export interface UploadDataItem {
    taskId: number;
    deadline: string;
    submitId: number;
    name: string;
    href: string;
}

export interface UploadData {
    [key: number]: UploadDataItem;
}

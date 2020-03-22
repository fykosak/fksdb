export interface UploadDataItem {
    taskId: number;
    deadline: string | null;
    submitId: number;
    name: string;
    href: string | null;
}

export interface UploadData {
    [key: number]: UploadDataItem;
}

import { Component, OnInit } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';

@Component({
  selector: 'app-file-upload',
  templateUrl: './file-upload.component.html',
  styleUrls: ['./file-upload.component.css']
})
export class FileUploadComponent implements OnInit {
  BATCH_SIZE: number = 20;
  allFileList: Array<any>;
  startFrameNumber: number = 0;
  numRepetition: number = 1;
  constructor(private http: HttpClient) {
    this.allFileList = Array();
  }

  ngOnInit(): void { }

  sendFileList(allFileList:Array<any>, i: number): void {
    let fileList = allFileList[i];
    const formData: FormData = new FormData();
    for(let j = 0; j < fileList.length; j++) {
      const file = fileList[j];
      formData.append('filesToUpload[]', file, file.name);

    }
    formData.append('numRepetition', this.numRepetition.toString());
    const url = 'http://localhost:8201/apps/video_creator/video_creator.php';
    const httpOptions = {
      // headers: new HttpHeaders({ 'Content-Type': 'application/json' })
    };
    this.http.post(url, formData, httpOptions).subscribe(result => console.log(result))
    i++;
    if (i < allFileList.length) {
      this.sendFileList(allFileList, i);
    }
    else {
      console.log('Finished.')
    }
  }

  onBrowseBtnClick(event: any): void {
    const fileListInput = event.target.files;
    let fileList = Array();
    let numberAddedFiles = 0;
    for (let i = 0; i < fileListInput.length; i++) {
      fileList.push(fileListInput[i]);
      numberAddedFiles++;
      if (numberAddedFiles >= this.BATCH_SIZE) {
        this.allFileList.push(fileList);
        fileList = [];
        numberAddedFiles = 0;
      }
    }
    if (numberAddedFiles > 0) {
      this.allFileList.push(fileList);
    }
  }
  
  onSubmitBtnClick(event: any): void {
    this.sendFileList(this.allFileList, 0);
    this.allFileList = [];
  }
}
